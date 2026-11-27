<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderOperationsService;
use Illuminate\Http\Request;

class OrderActionsController extends Controller
{
    public function status(Request $request, Order $order, OrderOperationsService $operations)
    {
        $this->authorizeOrder($request, $order);
        $operations->refresh($order);
        $order->refresh();

        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'supplier_status' => $order->supplier_status,
            'progress' => $operations->progress($order),
            'can_request_refill' => $operations->canRequestRefill($order),
            'can_request_cancel' => $operations->canRequestCancel($order),
            'terminal' => $order->isTerminal(),
        ]);
    }

    public function cancel(Request $request, Order $order, OrderOperationsService $operations)
    {
        $this->authorizeOrder($request, $order);
        $result = $operations->requestCancel($order, $request->user());

        return $result['ok']
            ? back()->with('status', $result['message'])
            : back()->withErrors(['order' => $result['message']]);
    }

    public function refill(Request $request, Order $order, OrderOperationsService $operations)
    {
        $this->authorizeOrder($request, $order);
        $result = $operations->requestRefill($order, $request->user());

        return $result['ok']
            ? back()->with('status', $result['message'])
            : back()->withErrors(['order' => $result['message']]);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 403);
    }
}
