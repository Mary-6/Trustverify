<?php

namespace App\Services\Suppliers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierLog;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Http;

class JustAnotherPanelSupplierAdapter implements SupplierAdapter
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

    private function redact(string $value): string
    {
        $key = (string) config('justanotherpanel.api_key');

        return $key === '' ? $value : str_replace($key, '***', $value);
    }
}
