<?php

namespace App\Services;

use App\Jobs\SubmitOrderToSupplier;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\MarketplaceNotification;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    public function __construct(
        private PricingService $pricing,
        private WalletService $wallets,
        private AuditLogger $audit,
    ) {}

    public function place(User $user, Product $product, array $payload, int $quantity, ?string $idempotencyKey = null, ?string $notes = null): Order
    {
        if (! $product->isAvailable()) {
            throw new RuntimeException('This product is currently unavailable.');
        }

        $quantity = max(1, $quantity);
        if ($product->min_order && $quantity < $product->min_order) {
            throw new RuntimeException("Minimum order quantity is {$product->min_order}.");
        }
        if ($product->max_order && $quantity > $product->max_order) {
            throw new RuntimeException("Maximum order quantity is {$product->max_order}.");
        }

        $adapter = $product->supplier_adapter ?: $product->supplier;
        if ($product->type === 'smm_service' && $adapter === 'justanotherpanel' && trim((string) ($payload['link'] ?? '')) === '') {
            throw new RuntimeException('A target link is required for this SMM service.');
        }

        $quote = $this->pricing->quote($product, $quantity);
        $walletCurrency = strtoupper((string) ($this->wallets->walletFor($user)->currency ?: 'XAF'));
        if ($walletCurrency !== strtoupper((string) ($quote['currency'] ?: 'XAF'))) {
            throw new RuntimeException('This product is not available in your wallet currency.');
        }

        $key = $idempotencyKey ?: (string) Str::uuid();

        $result = DB::transaction(function () use ($user, $product, $payload, $quantity, $quote, $key, $notes) {
            $existing = Order::where('user_id', $user->id)->where('idempotency_key', $key)->first();
            if ($existing) {
                return ['order' => $existing, 'created' => false];
            }

            $this->wallets->debit($user, $quote['total_price'], 'purchase', [
                'idempotency_key' => 'order-'.$key,
                'reference' => 'order-pending',
                'metadata' => ['product_id' => $product->id, 'quantity' => $quantity],
            ]);

            $order = Order::create([
                'order_number' => $this->orderNumber(),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'type' => $product->type,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $quote['unit_price'],
                'total_price' => $quote['total_price'],
                'currency' => $quote['currency'],
                'supplier_cost' => $quote['supplier_cost'],
                'status' => Order::STATUS_PENDING,
                'idempotency_key' => $key,
                'payload' => $payload,
                'customer_notes' => $notes,
                'paid_at' => now(),
            ]);

            $this->recordStatus($order, Order::STATUS_PENDING, 'Order created and paid from wallet.');
            $this->audit->log('order.created', $order, ['total_price' => $order->total_price, 'currency' => $order->currency], $user);

            return ['order' => $order, 'created' => true];
        });

        $order = $result['order'];
        if ($result['created'] && $this->shouldSubmitAutomatically($product)) {
            SubmitOrderToSupplier::dispatch($order->id)->afterCommit();
        }

        return $order->fresh(['histories', 'product']);
    }

    public function refund(Order $order, ?User $actor = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === Order::STATUS_REFUNDED) {
                return $order;
            }

            if ($order->status === Order::STATUS_COMPLETED) {
                throw new RuntimeException('Completed orders cannot be refunded through the standard wallet refund flow.');
            }

            $this->wallets->credit($order->user, $order->total_price, WalletTransaction::TYPE_REFUND, [
                'idempotency_key' => 'refund-'.$order->idempotency_key,
                'reference' => $order->order_number,
                'metadata' => ['order_id' => $order->id, 'reason' => $note ?: 'Administrator refund'],
                'created_by' => $actor?->id,
            ]);

            $order->forceFill([
                'status' => Order::STATUS_REFUNDED,
                'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').($note ?: 'Refunded to wallet by administrator.')),
            ])->save();

            $this->recordStatus($order, Order::STATUS_REFUNDED, $note ?: 'Refunded to wallet by administrator.', $actor);
            $this->audit->log('order.refunded', $order, ['note' => $note, 'amount' => $order->total_price], $actor);
            $this->notifyUser($order, 'Order refunded', 'Order '.$order->order_number.' was refunded to your wallet.');

            return $order;
        });
    }

    public function cancel(Order $order, ?User $actor = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === Order::STATUS_CANCELLED) {
                return $order;
            }

            if ($order->isTerminal()) {
                throw new RuntimeException('Only open orders can be cancelled.');
            }

            if ($order->supplier_reference || $order->submitted_at || $order->status !== Order::STATUS_PENDING) {
                throw new RuntimeException('This order has already been submitted to the provider and cannot be cancelled here.');
            }

            $this->wallets->credit($order->user, $order->total_price, WalletTransaction::TYPE_REFUND, [
                'idempotency_key' => 'cancel-'.$order->idempotency_key,
                'reference' => $order->order_number,
                'metadata' => ['order_id' => $order->id, 'reason' => $note ?: 'Customer cancellation'],
                'created_by' => $actor?->id,
            ]);

            $order->forceFill([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').($note ?: 'Cancelled by customer.')),
            ])->save();

            $this->recordStatus($order, Order::STATUS_CANCELLED, $note ?: 'Cancelled by customer.', $actor);
            $this->audit->log('order.cancelled', $order, ['note' => $note], $actor);
            $this->notifyUser($order, 'Order cancelled', 'Order '.$order->order_number.' was cancelled and refunded to your wallet.');

            return $order;
        });
    }

    public function recordStatus(Order $order, string $status, ?string $note = null, ?User $actor = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $status,
            'note' => $note,
            'created_by' => $actor?->id,
        ]);
    }

    private function notifyUser(Order $order, string $title, string $message): void
    {
        $order->user?->notify(new MarketplaceNotification(
            $title,
            $message,
            route('dashboard.orders.show', $order),
            ['order_id' => $order->id, 'order_number' => $order->order_number, 'status' => $order->status],
        ));
    }

    private function shouldSubmitAutomatically(Product $product): bool
    {
        $adapter = $product->supplier_adapter ?: $product->supplier;

        if (! in_array($adapter, ['textverified', 'herosms', 'justanotherpanel'], true)) {
            return false;
        }

        $meta = $product->meta ?? [];

        $service = $meta['service_name'] ?? $meta['service'] ?? $product->supplier_product_id;

        if ($adapter === 'textverified') {
            return $product->type === 'virtual_number' && ! empty($service);
        }

        if ($adapter === 'justanotherpanel') {
            return $product->type === 'smm_service' && ! empty($product->supplier_product_id);
        }

        return $product->type === 'virtual_number'
            && ! empty($service)
            && ! empty($meta['country_id'] ?? $meta['country'] ?? null);
    }

    private function orderNumber(): string
    {
        do {
            $number = 'TVS-'.strtoupper(Str::random(10));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
