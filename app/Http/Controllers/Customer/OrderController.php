<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use RuntimeException;

class OrderController extends Controller
{
    public function create(Request $request, Product $product, WalletService $wallets)
    {
        abort_unless($product->isActiveForPurchase(), 404);

        $min = (int) ($product->min_order ?? 1);
        $max = $product->max_order ? (int) $product->max_order : null;
        $quantity = $request->integer('quantity', $min);
        if ($quantity < $min || ($max !== null && $quantity > $max)) {
            $quantity = $min;
        }

        return view('customer.orders.create', [
            'product' => $product->load('category'),
            'countries' => Country::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'wallet' => $wallets->walletFor($request->user()),
            'initialQuantity' => $quantity,
        ]);
    }

    public function store(Request $request, Product $product, OrderService $orders)
    {
        abort_unless($product->isActiveForPurchase(), 404);

        $data = $request->validate([
            'link' => ['nullable', 'string', 'max:2048'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'service' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $order = $orders->place(
                $request->user(),
                $product,
                [
                    'link' => $data['link'] ?? null,
                    'country_id' => $data['country_id'] ?? null,
                    'service' => $data['service'] ?? null,
                ],
                (int) $data['quantity'],
                $data['idempotency_key'] ?? null,
                $data['notes'] ?? null,
            );
        } catch (RuntimeException $e) {
            if ($this->isInsufficientBalance($e)) {
                return $this->depositRedirect($product, (int) $data['quantity']);
            }

            return back()->withInput()->withErrors(['order' => $e->getMessage()]);
        }

        return redirect()->route('dashboard.orders.show', $order)->with('status', 'Order '.$order->order_number.' was created.');
    }

    private function isInsufficientBalance(RuntimeException $e): bool
    {
        return str_contains(strtolower($e->getMessage()), 'insufficient wallet balance');
    }

    private function depositRedirect(Product $product, int $quantity)
    {
        $returnTo = route('dashboard.orders.create', $product, false).'?quantity='.$quantity;

        return redirect()->route('dashboard.wallet.index', ['return_to' => $returnTo])
            ->with('error', 'Insufficient wallet balance. Deposit funds to continue this order.');
    }
}
