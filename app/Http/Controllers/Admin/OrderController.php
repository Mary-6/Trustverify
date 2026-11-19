<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\SupplierFulfillmentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'product'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($inner) use ($request) {
                $inner->where('order_number', 'like', '%'.$request->string('q').'%')
                    ->orWhere('supplier_reference', 'like', '%'.$request->string('q').'%');
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', ['order' => $order->load(['user', 'product', 'histories'])]);
    }

    public function update(Request $request, Order $order, OrderService $orders, SupplierFulfillmentService $fulfillment)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:pending,processing,submitted,completed,failed,cancelled,refunded'],
            'supplier_reference' => ['nullable', 'string', 'max:120'],
            'supplier_status' => ['nullable', 'string', 'max:120'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'action' => ['nullable', 'in:status,resubmit,sync_status,refund'],
        ]);

        if (($data['action'] ?? null) === 'refund' || ($data['status'] ?? null) === Order::STATUS_REFUNDED) {
            if ($order->status !== Order::STATUS_REFUNDED) {
                $orders->refund($order, $request->user(), $request->input('status_note', 'Refunded by administrator.'));

                return back()->with('status', 'Order refunded to wallet.');
            }

            if (($data['action'] ?? null) === 'refund') {
                return back()->with('status', 'Order was already refunded.');
            }
        }

        if (($data['action'] ?? null) === 'resubmit' && ! $order->isTerminal()) {
            $fulfillment->submit($order);
            return back()->with('status', 'Order resubmission attempted.');
        }

        if (($data['action'] ?? null) === 'sync_status' && $order->supplier_reference) {
            $fulfillment->syncStatus($order);
            return back()->with('status', 'Supplier status sync attempted.');
        }

        $data['status'] = $data['status'] ?? $order->status;

        $order->fill([
            'status' => $data['status'],
            'supplier_reference' => $data['supplier_reference'] ?? $order->supplier_reference,
            'supplier_status' => $data['supplier_status'] ?? $order->supplier_status,
            'admin_notes' => $data['admin_notes'] ?? $order->admin_notes,
        ]);

        $statusChanged = $order->isDirty('status');

        if ($statusChanged) {
            if ($data['status'] === Order::STATUS_COMPLETED && ! $order->completed_at) {
                $order->completed_at = now();
            }
            if ($data['status'] === Order::STATUS_CANCELLED && ! $order->cancelled_at) {
                $order->cancelled_at = now();
            }
        }

        $order->save();

        if ($statusChanged || $request->filled('status_note')) {
            $orders->recordStatus($order, $data['status'], $request->input('status_note', 'Status updated by administrator.'), $request->user());
        }

        return back()->with('status', 'Order updated.');
    }
}
