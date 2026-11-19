<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WalletTransaction;
use App\Services\Suppliers\SupplierManager;
use Illuminate\Support\Facades\DB;
use Throwable;

class SupplierFulfillmentService
{
    public function __construct(
        private SupplierManager $suppliers,
        private WalletService $wallets,
        private OrderService $orders,
        private AuditLogger $audit,
    ) {}

    public function submit(Order $order): void
    {
        $order->loadMissing('product');
        $product = $order->product;

        if (! $product || $order->isTerminal() || $order->supplier_reference) {
            return;
        }

        try {
            $adapter = $this->suppliers->adapterFor($product);
        } catch (Throwable $e) {
            $this->orders->recordStatus($order, $order->status, 'Supplier adapter unavailable: '.$e->getMessage());
            return;
        }

        if (! $adapter->supports($product)) {
            $this->orders->recordStatus($order, $order->status, 'Awaiting manual fulfillment or supplier endpoint configuration.');
            return;
        }

        $order->forceFill(['status' => Order::STATUS_PROCESSING, 'submitted_at' => now()])->save();
        $this->orders->recordStatus($order, Order::STATUS_PROCESSING, 'Submitted to '.$adapter->name().'.');

        try {
            $result = $adapter->placeOrder($order->fresh('product'));
        } catch (Throwable $e) {
            $this->orders->recordStatus($order, Order::STATUS_PROCESSING, 'Supplier submission error: '.$e->getMessage());
            return;
        }

        if ($result->status === 'success') {
            $order->forceFill([
                'status' => $result->reference ? Order::STATUS_SUBMITTED : Order::STATUS_PROCESSING,
                'supplier_reference' => $result->reference,
                'supplier_status' => $result->data['supplier_status'] ?? $result->data['state'] ?? $result->data['status'] ?? null,
                'delivery_data' => ! empty($result->data['delivery'])
                    ? array_merge($order->delivery_data ?? [], $result->data['delivery'])
                    : $order->delivery_data,
            ])->save();
            $this->orders->recordStatus($order, $order->status, $result->message ?: 'Supplier accepted the order.');
            return;
        }

        if ($result->safeToRefund) {
            DB::transaction(function () use ($order, $result) {
                $order->forceFill(['status' => Order::STATUS_REFUNDED, 'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').'Supplier failed before fulfillment; wallet refunded: '.$result->message)])->save();
                $this->orders->recordStatus($order, Order::STATUS_REFUNDED, $result->message);
                $this->wallets->credit($order->user, $order->total_price, WalletTransaction::TYPE_REFUND, [
                    'idempotency_key' => 'refund-'.$order->idempotency_key,
                    'reference' => $order->order_number,
                    'metadata' => ['order_id' => $order->id, 'reason' => $result->message],
                ]);
            });
            $this->audit->log('order.refunded', $order, ['reason' => $result->message]);
            return;
        }

        $this->orders->recordStatus($order, Order::STATUS_PROCESSING, $result->message ?: 'Supplier fulfillment is pending.');
    }

    public function syncStatus(Order $order): void
    {
        $order->loadMissing('product');
        $product = $order->product;

        if (! $product || ! $order->supplier_reference || $order->isTerminal()) {
            return;
        }

        try {
            $result = $this->suppliers->adapterFor($product)->orderStatus($order);
        } catch (Throwable $e) {
            $this->orders->recordStatus($order, $order->status, 'Supplier status sync error: '.$e->getMessage());
            return;
        }

        $remote = strtolower((string) ($result->data['status'] ?? $result->data['state'] ?? ''));
        $status = match (true) {
            str_contains($remote, 'complete') || str_contains($remote, 'success') || str_contains($remote, 'finished') => Order::STATUS_COMPLETED,
            str_contains($remote, 'cancel') => Order::STATUS_CANCELLED,
            str_contains($remote, 'fail') || str_contains($remote, 'error') || str_contains($remote, 'reject') => Order::STATUS_FAILED,
            str_contains($remote, 'progress') || str_contains($remote, 'process') => Order::STATUS_PROCESSING,
            default => $order->status,
        };

        $order->forceFill([
            'supplier_status' => $result->data['supplier_status'] ?? $result->data['state'] ?? $result->data['status'] ?? $order->supplier_status,
            'status' => $status,
            'delivery_data' => ! empty($result->data['delivery'])
                ? array_merge($order->delivery_data ?? [], $result->data['delivery'])
                : $order->delivery_data,
            'completed_at' => $status === Order::STATUS_COMPLETED ? ($order->completed_at ?? now()) : $order->completed_at,
        ])->save();

        $this->orders->recordStatus($order, $status, 'Supplier status synced.');
    }
}
