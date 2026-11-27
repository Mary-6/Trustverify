<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WalletTransaction;
use App\Notifications\MarketplaceNotification;
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

        if ($order->submitted_at && $order->status === Order::STATUS_PROCESSING) {
            $this->orders->recordStatus($order, $order->status, 'Supplier submission was already attempted without a reference; manual review is required to avoid duplicate supplier orders.');
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
            $this->notifyStatus($order, $order->status);
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
            $this->notifyStatus($order, Order::STATUS_REFUNDED);
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
            return;
        }

        if ($result->status !== 'success') {
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

        $previousStatus = $order->status;
        $previousDelivery = $order->delivery_data ?? [];
        $supplierStatus = $result->data['supplier_status'] ?? $result->data['state'] ?? $result->data['status'] ?? $order->supplier_status;
        $deliveryData = ! empty($result->data['delivery'])
            ? array_merge($order->delivery_data ?? [], $result->data['delivery'])
            : $order->delivery_data;
        $deliveryData = $deliveryData ?? [];
        $smsReceived = $order->type === 'virtual_number'
            && ! empty($deliveryData['sms_code'])
            && empty($previousDelivery['sms_code']);

        $changed = $order->status !== $status
            || $order->supplier_status !== $supplierStatus
            || ($order->delivery_data ?? []) != $deliveryData;

        if (! $changed) {
            return;
        }

        $refundCancelled = $status === Order::STATUS_CANCELLED
            && in_array($previousStatus, [Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_SUBMITTED], true)
            && $order->supplier_reference;

        DB::transaction(function () use ($order, $status, $supplierStatus, $deliveryData, $remote, $refundCancelled) {
            $note = 'Supplier status synced: '.($remote !== '' ? $remote : $status);

            $order->forceFill([
                'supplier_status' => $supplierStatus,
                'status' => $status,
                'delivery_data' => $deliveryData,
                'completed_at' => $status === Order::STATUS_COMPLETED ? ($order->completed_at ?? now()) : $order->completed_at,
                'cancelled_at' => $status === Order::STATUS_CANCELLED ? ($order->cancelled_at ?? now()) : $order->cancelled_at,
            ])->save();

            if ($refundCancelled) {
                $this->wallets->credit($order->user, $order->total_price, WalletTransaction::TYPE_REFUND, [
                    'idempotency_key' => 'refund-'.$order->idempotency_key,
                    'reference' => $order->order_number,
                    'metadata' => ['order_id' => $order->id, 'reason' => 'Supplier cancelled the order'],
                ]);
                $note .= ' Wallet refunded after supplier cancellation.';
            }

            $this->orders->recordStatus($order, $status, $note);
        });

        if ($previousStatus !== $status) {
            $this->notifyStatus($order, $status);
        }

        if ($smsReceived) {
            $this->notifySms($order, $deliveryData);
        }
    }

    private function notifyStatus(Order $order, string $status): void
    {
        $order->user?->notify(new MarketplaceNotification(
            'Order status updated',
            'Order '.$order->order_number.' is now '.str_replace('_', ' ', $status).'.',
            route('dashboard.orders.show', $order),
            ['order_id' => $order->id, 'order_number' => $order->order_number, 'status' => $status],
        ));
    }

    private function notifySms(Order $order, array $delivery): void
    {
        $code = $delivery['sms_code'] ?? null;
        if (! $code) {
            return;
        }

        $order->user?->notify(new MarketplaceNotification(
            'SMS received',
            'A verification code arrived for '.$order->order_number.'.',
            route('dashboard.verification.orders.show', $order),
            ['order_id' => $order->id, 'order_number' => $order->order_number, 'sms_code' => $code],
        ));
    }
}
