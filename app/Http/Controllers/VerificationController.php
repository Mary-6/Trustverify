<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\SupplierFulfillmentService;
use App\Services\Suppliers\SupplierManager;
use App\Services\WalletService;
use App\Support\Money;
use App\Support\VerificationCatalogue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $wallet = $request->user()
            ? app(WalletService::class)->walletFor($request->user())
            : null;

        return view('pages.verification', [
            'catalog' => VerificationCatalogue::catalog(),
            'wallet' => $wallet,
        ]);
    }

    public function store(Request $request, OrderService $orders)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'app_slug' => ['required', 'string', 'max:40'],
            'country_code' => ['required', 'string', 'max:4'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ]);

        $product = Product::active()->where('type', 'virtual_number')->findOrFail($data['product_id']);

        if (! VerificationCatalogue::selectionMatches($product, $data['app_slug'], $data['country_code'])) {
            return back()->withErrors(['product' => 'The selected verification option is no longer available.']);
        }

        try {
            $providerReady = app(SupplierManager::class)->adapterFor($product)->supports($product);
        } catch (\Throwable) {
            $providerReady = false;
        }

        if (! $providerReady) {
            return back()->withErrors(['product' => 'This verification provider is unavailable right now. Please try another app or country.']);
        }

        $wallet = app(WalletService::class)->walletFor($request->user());
        if (Money::compare($wallet->balance, $product->price) < 0) {
            return back()->withErrors(['wallet' => 'Insufficient wallet balance. Add funds before getting a number.']);
        }

        try {
            $order = $orders->place(
                $request->user(),
                $product,
                [
                    'verification' => [
                        'app' => $data['app_slug'],
                        'country' => strtoupper($data['country_code']),
                    ],
                ],
                1,
                $data['idempotency_key'] ?? 'verification-'.Str::random(16),
                'SMS verification number request.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }

        return redirect()->route('dashboard.verification.orders.show', $order)
            ->with('status', 'Verification '.$order->order_number.' was created.');
    }

    public function show(Request $request, Order $order, SupplierFulfillmentService $fulfillment)
    {
        $this->authorizeOrder($request, $order);

        return view('customer.verification.show', [
            'order' => $order->load('product'),
            'delivery' => $order->delivery_data ?? [],
            'statusUrl' => route('dashboard.verification.orders.status', $order),
            'cancelUrl' => route('dashboard.verification.orders.cancel', $order),
            'backUrl' => route('verification.index'),
            'canCancel' => $this->canCancel($order),
        ]);
    }

    public function status(Request $request, Order $order, SupplierFulfillmentService $fulfillment)
    {
        $this->authorizeOrder($request, $order);

        if ($order->supplier_reference && ! $order->isTerminal()) {
            $fulfillment->syncStatus($order);
            $order->refresh();
        }

        $delivery = $order->delivery_data ?? [];

        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'supplier_status' => $order->supplier_status,
            'state' => $delivery['state'] ?? null,
            'number' => $delivery['number'] ?? null,
            'sms_code' => $delivery['sms_code'] ?? null,
            'sms_content' => $delivery['sms_content'] ?? null,
            'ends_at' => $delivery['ends_at'] ?? null,
            'terminal' => $order->isTerminal(),
            'can_cancel' => $this->canCancel($order),
        ]);
    }

    public function cancel(Request $request, Order $order, OrderService $orders)
    {
        $this->authorizeOrder($request, $order);

        try {
            $orders->cancel($order, $request->user(), 'Cancelled by customer before supplier submission.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return redirect()->route('dashboard.verification.orders.show', $order->fresh())
            ->with('status', 'Verification cancelled. Any wallet refund has been applied.');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless($order->type === 'virtual_number' && $order->user_id === $request->user()->id, 403);
    }

    private function canCancel(Order $order): bool
    {
        return $order->status === Order::STATUS_PENDING
            && ! $order->isTerminal()
            && ! $order->supplier_reference
            && ! $order->submitted_at;
    }
}
