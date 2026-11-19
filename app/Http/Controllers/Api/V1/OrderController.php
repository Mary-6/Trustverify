<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use AuthorizesApiAbilities;

    public function index(Request $request)
    {
        $this->authorizeAbility($request, 'orders:read');
        $orders = $request->user()->orders()->with('product')->latest()->paginate(min((int) $request->integer('per_page', 25), 100));

        return response()->json([
            'data' => $orders->getCollection()->map(fn (Order $order) => $this->present($order)),
            'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()],
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeAbility($request, 'orders:read');
        abort_unless($order->user_id === $request->user()->id, 404);

        return response()->json(['data' => $this->present($order->load('product', 'histories'), true)]);
    }

    private function present(Order $order, bool $detail = false): array
    {
        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'product' => $order->product_name,
            'type' => $order->type,
            'quantity' => $order->quantity,
            'total_price' => (float) $order->total_price,
            'currency' => $order->currency,
            'status' => $order->status,
            'supplier_reference' => $order->supplier_reference,
            'created_at' => $order->created_at?->toISOString(),
        ];

        if ($detail) {
            $data['supplier_status'] = $order->supplier_status;
            $data['paid_at'] = $order->paid_at?->toISOString();
            $data['submitted_at'] = $order->submitted_at?->toISOString();
            $data['completed_at'] = $order->completed_at?->toISOString();
            $data['histories'] = $order->histories->map(fn ($history) => [
                'status' => $history->status,
                'note' => $history->note,
                'created_at' => $history->created_at?->toISOString(),
            ])->values();
        }

        return $data;
    }
}
