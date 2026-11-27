<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\Suppliers\SupplierAdapter;
use App\Services\Suppliers\SupplierManager;
use App\Services\Suppliers\SupportsOrderActions;
use Throwable;

class OrderOperationsService
{
    public function __construct(
        private SupplierManager $suppliers,
        private SupplierFulfillmentService $fulfillment,
        private OrderService $orders,
        private AuditLogger $audit,
    ) {}

    public function refresh(Order $order): void
    {
        $order->loadMissing('product');

        if ($order->supplier_reference && ! $order->isTerminal()) {
            try {
                $this->fulfillment->syncStatus($order);
            } catch (Throwable) {
                // Status refresh is best-effort; the page still renders stored state.
            }
        }
    }

    public function progress(Order $order): ?array
    {
        if ($order->type !== 'smm_service') {
            return null;
        }

        $delivery = $order->delivery_data ?? [];
        $start = $this->number($delivery['start_count'] ?? null);
        $remains = $this->number($delivery['remains'] ?? null);
        $quantity = (float) $order->quantity;

        $delivered = $remains !== null ? max(0, $quantity - $remains) : null;
        $percent = ($delivered !== null && $quantity > 0) ? min(100, round(($delivered / $quantity) * 100, 1)) : null;

        return array_filter([
            'start_count' => $start,
            'remains' => $remains,
            'delivered' => $delivered,
            'percent' => $percent,
            'charge' => $this->number($delivery['charge'] ?? null),
            'currency' => $delivery['currency'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function canRequestRefill(Order $order): bool
    {
        return $order->type === 'smm_service'
            && $order->status === Order::STATUS_COMPLETED
            && ! empty($order->supplier_reference)
            && empty(($order->delivery_data ?? [])['refill_requested_at'])
            && $this->adapterSupports($order, 'canRefill');
    }

    public function canRequestCancel(Order $order): bool
    {
        return $order->type === 'smm_service'
            && in_array($order->status, [Order::STATUS_PROCESSING, Order::STATUS_SUBMITTED], true)
            && ! empty($order->supplier_reference)
            && empty(($order->delivery_data ?? [])['cancel_requested_at'])
            && $this->adapterSupports($order, 'canCancel');
    }

    public function requestRefill(Order $order, User $actor): array
    {
        $this->refresh($order);
        $order->refresh();

        if (! $this->canRequestRefill($order)) {
            return ['ok' => false, 'message' => 'This order is not eligible for a refill right now.'];
        }

        $adapter = $this->adapterFor($order);
        if (! $adapter instanceof SupportsOrderActions || ! $adapter->canRefill($order->product)) {
            return ['ok' => false, 'message' => 'Refill is not available for this service.'];
        }

        try {
            $result = $adapter->requestRefill($order);
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Unable to request a refill right now. Please try again later.'];
        }

        if ($result->status !== 'success') {
            return ['ok' => false, 'message' => $result->message ?: 'The supplier could not accept the refill request.'];
        }

        $delivery = array_merge($order->delivery_data ?? [], [
            'refill_id' => $result->reference,
            'refill_status' => $result->data['status'] ?? 'requested',
            'refill_requested_at' => now()->toISOString(),
        ]);

        $order->forceFill(['delivery_data' => $delivery])->save();
        $this->orders->recordStatus($order, $order->status, 'Refill requested with supplier.');
        $this->audit->log('order.refill_requested', $order, ['refill_id' => $result->reference], $actor);
        $this->notify($order, 'Refill requested', 'Your refill request for '.$order->order_number.' was sent to the supplier.');

        return ['ok' => true, 'message' => 'Refill requested. We will update the order if the supplier confirms it.'];
    }

    public function requestCancel(Order $order, User $actor): array
    {
        $this->refresh($order);
        $order->refresh();

        if (! $this->canRequestCancel($order)) {
            return ['ok' => false, 'message' => 'This order is not eligible for cancellation right now.'];
        }

        $adapter = $this->adapterFor($order);
        if (! $adapter instanceof SupportsOrderActions || ! $adapter->canCancel($order->product)) {
            return ['ok' => false, 'message' => 'Cancellation is not available for this service.'];
        }

        try {
            $result = $adapter->cancelOrder($order);
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Unable to request cancellation right now. Please try again later.'];
        }

        if ($result->status !== 'success') {
            return ['ok' => false, 'message' => $result->message ?: 'The supplier could not accept the cancellation request.'];
        }

        $delivery = array_merge($order->delivery_data ?? [], [
            'cancel_status' => $result->data['status'] ?? 'requested',
            'cancel_result' => $result->data['cancel_result'] ?? null,
            'cancel_requested_at' => now()->toISOString(),
        ]);

        $order->forceFill(['delivery_data' => $delivery])->save();
        $this->orders->recordStatus($order, $order->status, 'Cancellation requested with supplier.');
        $this->audit->log('order.cancel_requested', $order, ['result' => $result->data['cancel_result'] ?? null], $actor);
        $this->notify($order, 'Cancellation requested', 'Your cancellation request for '.$order->order_number.' was sent to the supplier.');

        return ['ok' => true, 'message' => 'Cancellation requested. If the supplier confirms before delivery starts, the order will be cancelled and refunded automatically.'];
    }

    private function adapterFor(Order $order): ?SupplierAdapter
    {
        $order->loadMissing('product');

        try {
            return $this->suppliers->adapterFor($order->product);
        } catch (Throwable) {
            return null;
        }
    }

    private function adapterSupports(Order $order, string $method): bool
    {
        $adapter = $this->adapterFor($order);
        $order->loadMissing('product');

        return $adapter instanceof SupportsOrderActions && $adapter->{$method}($order->product);
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function notify(Order $order, string $title, string $message): void
    {
        $order->user?->notify(new MarketplaceNotification(
            $title,
            $message,
            route('dashboard.orders.show', $order),
            ['order_id' => $order->id, 'order_number' => $order->order_number, 'status' => $order->status],
        ));
    }
}
