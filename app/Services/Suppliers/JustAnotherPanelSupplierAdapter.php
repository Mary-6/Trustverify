<?php

namespace App\Services\Suppliers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierLog;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Http;

class JustAnotherPanelSupplierAdapter implements SupplierAdapter, SupportsOrderActions
{
    public function name(): string
    {
        return 'justanotherpanel';
    }

    public function supports(Product $product): bool
    {
        $forPanel = ($product->supplier === 'justanotherpanel' || $product->supplier_adapter === 'justanotherpanel')
            && $product->type === 'smm_service'
            && ! empty($product->supplier_product_id);

        return $forPanel && $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return ! empty(config('justanotherpanel.api_key'))
            && ! empty(config('justanotherpanel.base_url'))
            && $this->settingEnabled('justanotherpanel');
    }

    public function placeOrder(Order $order): SupplierResult
    {
        $product = $order->product;
        $link = trim((string) ($order->payload['link'] ?? ''));
        $service = (int) ($product?->supplier_product_id ?? 0);

        if ($link === '') {
            return SupplierResult::failed('A target link is required before submitting to JustAnotherPanel.', true);
        }

        if (! $service) {
            return SupplierResult::failed('JustAnotherPanel service ID is not configured for this product.', true);
        }

        $params = [
            'service' => $service,
            'link' => $link,
            'quantity' => (int) $order->quantity,
            'idempotency_key' => $this->supplierIdempotencyKey($order),
        ];

        $response = $this->call('add', $params, $order->id, 'order.create');
        if (! $response->successful()) {
            return SupplierResult::failed('JustAnotherPanel request failed.', $response->clientError(), ['http_status' => $response->status()]);
        }

        $data = $response->json() ?? [];
        if (isset($data['error'])) {
            return SupplierResult::failed((string) $data['error'], true, ['supplier_error' => $data['error']]);
        }

        $reference = $data['order'] ?? $data['id'] ?? null;
        if (! $reference) {
            return SupplierResult::failed('JustAnotherPanel response did not include an order ID.', false);
        }

        return SupplierResult::success((string) $reference, [
            'status' => 'processing',
            'supplier_status' => 'Pending',
            'state' => 'Pending',
            'delivery' => array_filter([
                'service_id' => $service,
                'quantity' => (int) $order->quantity,
            ], fn ($value) => $value !== null && $value !== ''),
        ], 'JustAnotherPanel accepted the order.');
    }

    public function orderStatus(Order $order): SupplierResult
    {
        if (! $order->supplier_reference) {
            return SupplierResult::unavailable('JustAnotherPanel supplier reference is missing.');
        }

        $response = $this->call('status', ['order' => $order->supplier_reference], $order->id, 'order.status');
        if (! $response->successful()) {
            return SupplierResult::failed('JustAnotherPanel status request failed.', false, ['http_status' => $response->status()]);
        }

        $data = $response->json() ?? [];
        if (isset($data['error'])) {
            return SupplierResult::failed((string) $data['error'], false, ['supplier_error' => $data['error']]);
        }

        $statusData = $data;
        if (isset($data['order']) && is_array($data['order'])) {
            $statusData = $data['order'];
        } elseif (isset($data[$order->supplier_reference]) && is_array($data[$order->supplier_reference])) {
            $statusData = $data[$order->supplier_reference];
        }

        $remote = (string) ($statusData['status'] ?? '');

        return SupplierResult::success($order->supplier_reference, [
            'status' => $this->mapStatus($remote),
            'supplier_status' => $remote !== '' ? $remote : null,
            'state' => $remote !== '' ? $remote : null,
            'delivery' => array_filter([
                'charge' => $statusData['charge'] ?? null,
                'start_count' => $statusData['start_count'] ?? null,
                'remains' => $statusData['remains'] ?? null,
                'currency' => $statusData['currency'] ?? null,
                'status' => $remote !== '' ? $remote : null,
            ], fn ($value) => $value !== null && $value !== ''),
        ], 'JustAnotherPanel status retrieved.');
    }

    public function canRefill(Product $product): bool
    {
        return $this->isConfigured() && (bool) (($product->meta ?? [])['refill'] ?? false);
    }

    public function canCancel(Product $product): bool
    {
        return $this->isConfigured() && (bool) (($product->meta ?? [])['cancel'] ?? false);
    }

    public function requestRefill(Order $order): SupplierResult
    {
        if (! $order->supplier_reference) {
            return SupplierResult::unavailable('JustAnotherPanel supplier reference is missing.');
        }

        $response = $this->call('refill', ['order' => $order->supplier_reference], $order->id, 'order.refill');
        if (! $response->successful()) {
            return SupplierResult::failed('JustAnotherPanel refill request failed.', false, ['http_status' => $response->status()]);
        }

        return $this->parseRefill($response->json() ?? [], $order);
    }

    public function cancelOrder(Order $order): SupplierResult
    {
        if (! $order->supplier_reference) {
            return SupplierResult::unavailable('JustAnotherPanel supplier reference is missing.');
        }

        $response = $this->call('cancel', ['orders' => $order->supplier_reference], $order->id, 'order.cancel');
        if (! $response->successful()) {
            return SupplierResult::failed('JustAnotherPanel cancel request failed.', false, ['http_status' => $response->status()]);
        }

        return $this->parseCancel($response->json() ?? [], $order);
    }

    public function catalog(): array
    {
        $response = $this->call('services', [], null, 'catalog');
        $data = $response->json();

        if (! $response->successful() || ! is_array($data) || isset($data['error'])) {
            return [];
        }

        return array_values(array_filter($data, fn ($service) => is_array($service) && isset($service['service'])));
    }

    public function accountBalance(): ?array
    {
        $response = $this->call('balance', [], null, 'account.balance');
        if (! $response->successful()) {
            return null;
        }

        $data = $response->json() ?? [];
        if (isset($data['error'])) {
            return null;
        }

        $balance = $data['balance'] ?? null;
        if (! is_numeric($balance)) {
            return null;
        }

        return [
            'balance' => (float) $balance,
            'currency' => strtoupper((string) ($data['currency'] ?? 'USD')),
        ];
    }

    private function call(string $action, array $params = [], ?int $orderId = null, string $logAction = 'request')
    {
        $started = microtime(true);
        $payload = ['key' => (string) config('justanotherpanel.api_key'), 'action' => $action] + $params;
        $url = $this->url((string) config('justanotherpanel.endpoint', '/api/v2'));

        $response = Http::acceptJson()
            ->timeout((int) config('justanotherpanel.timeout', 30))
            ->post($url, $payload);

        SupplierLog::create([
            'order_id' => $orderId,
            'supplier' => $this->name(),
            'adapter' => $this->name(),
            'action' => $logAction,
            'endpoint' => $url,
            'request_payload' => ['action' => $action] + $params,
            'response_payload' => $response->json() ?? ['raw' => $this->redact(substr($response->body(), 0, 4000))],
            'http_status' => $response->status(),
            'status' => $response->successful() ? 'success' : 'failed',
            'error_message' => $response->successful() ? null : $this->redact(substr($response->body(), 0, 1000)),
            'duration_ms' => (int) ((microtime(true) - $started) * 1000),
        ]);

        return $response;
    }

    private function settingEnabled(string $provider, bool $default = true): bool
    {
        return (bool) app(SettingsService::class)->get('supplier.'.$provider.'_enabled', $default);
    }

    private function url(string $endpoint): string
    {
        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        return rtrim((string) config('justanotherpanel.base_url'), '/').'/'.ltrim($endpoint, '/');
    }

    private function supplierIdempotencyKey(Order $order): string
    {
        return 'tvsjap'.str_pad((string) $order->id, 12, '0', STR_PAD_LEFT);
    }

    private function mapStatus(string $status): string
    {
        $status = strtolower($status);

        return match (true) {
            str_contains($status, 'complet'), str_contains($status, 'success'), str_contains($status, 'finish') => 'completed',
            str_contains($status, 'cancel'), str_contains($status, 'refund') => 'cancelled',
            str_contains($status, 'fail'), str_contains($status, 'error'), str_contains($status, 'reject') => 'failed',
            default => 'processing',
        };
    }

    private function parseRefill(array $data, Order $order): SupplierResult
    {
        if (isset($data['error'])) {
            return SupplierResult::failed((string) $data['error'], false, ['supplier_error' => $data['error']]);
        }

        $reference = (string) $order->supplier_reference;
        $refill = $data['refill'] ?? $data['id'] ?? null;

        if ($refill === null && isset($data['orders']) && is_array($data['orders'])) {
            foreach ($data['orders'] as $row) {
                if (is_array($row) && (string) ($row['order'] ?? '') === $reference) {
                    $refill = $row['refill'] ?? $row['id'] ?? null;
                    break;
                }
            }
        }

        if ($refill === null && isset($data[$reference]) && is_array($data[$reference])) {
            $refill = $data[$reference]['refill'] ?? $data[$reference]['id'] ?? null;
        }

        if ($refill === null && isset($data['order']) && (string) $data['order'] === $reference) {
            $refill = $data['refill'] ?? $data['id'] ?? null;
        }

        if ($refill === null) {
            return SupplierResult::failed('JustAnotherPanel did not return a refill ID.', false, ['supplier_response' => $data]);
        }

        return SupplierResult::success((string) $refill, [
            'status' => 'refill_requested',
            'supplier_status' => 'Refill requested',
            'refill_id' => $refill,
        ], 'Refill requested.');
    }

    private function parseCancel(array $data, Order $order): SupplierResult
    {
        $row = $this->cancelRow($data, $order);

        if ($row === null) {
            return SupplierResult::failed('JustAnotherPanel did not confirm cancellation.', false, ['supplier_response' => $data]);
        }

        if (isset($row['error'])) {
            return SupplierResult::failed((string) $row['error'], false, ['supplier_error' => $row['error'], 'supplier_response' => $data]);
        }

        $cancel = $row['cancel'] ?? $row['status'] ?? $row['result'] ?? null;
        $confirmed = $this->truthy($cancel) || (is_string($cancel) && stripos($cancel, 'cancel') !== false);

        if (! $confirmed) {
            return SupplierResult::failed('JustAnotherPanel did not confirm cancellation.', false, ['supplier_response' => $data]);
        }

        return SupplierResult::success($order->supplier_reference, [
            'status' => 'cancel_requested',
            'supplier_status' => 'Cancel requested',
            'cancel_result' => $row,
        ], 'Cancellation requested.');
    }

    private function cancelRow(array $data, Order $order): ?array
    {
        $reference = (string) $order->supplier_reference;

        if (isset($data[$reference]) && is_array($data[$reference])) {
            return $data[$reference];
        }

        if (isset($data['orders']) && is_array($data['orders'])) {
            foreach ($data['orders'] as $row) {
                if (is_array($row) && (string) ($row['order'] ?? $row['id'] ?? '') === $reference) {
                    return $row;
                }
            }
        }

        foreach ($data as $row) {
            if (is_array($row) && (string) ($row['order'] ?? $row['id'] ?? '') === $reference) {
                return $row;
            }
        }

        if (isset($data['order']) && (string) $data['order'] === $reference) {
            return $data;
        }

        return null;
    }

    private function truthy(mixed $value): bool
    {
        if ($value === true || $value === 1) {
            return true;
        }

        return is_string($value) && in_array(strtolower($value), ['1', 'true', 'success', 'cancelled', 'canceled', 'yes'], true);
    }

    private function redact(string $value): string
    {
        $key = (string) config('justanotherpanel.api_key');

        return $key === '' ? $value : str_replace($key, '***', $value);
    }
}
