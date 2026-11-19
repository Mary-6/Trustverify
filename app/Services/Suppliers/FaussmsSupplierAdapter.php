<?php

namespace App\Services\Suppliers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class FaussmsSupplierAdapter implements SupplierAdapter
{
    public function name(): string
    {
        return 'faussms';
    }

    public function supports(Product $product): bool
    {
        $forFaussms = ($product->supplier === 'faussms' || $product->supplier_adapter === 'faussms')
            && ! empty($product->supplier_product_id);

        return $forFaussms && $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return ! empty(config('faussms.api_key')) && ! empty(config('faussms.base_url'));
    }

    public function placeOrder(Order $order): SupplierResult
    {
        $endpoint = config('faussms.endpoints.place_order');
        if (! $endpoint) {
            return SupplierResult::unavailable('FAUSSMS order endpoint is not configured.');
        }

        $product = $order->product;
        $payload = [
            'service_id' => $product?->supplier_product_id,
            'link' => $order->payload['link'] ?? null,
            'quantity' => $order->quantity,
            'idempotency_key' => $order->idempotency_key,
        ];

        $response = $this->request('POST', $endpoint, $payload, $order->id);
        if (! $response->successful()) {
            return SupplierResult::failed('FAUSSMS request failed.', false, ['http_status' => $response->status()]);
        }

        $data = $response->json() ?? [];
        $reference = $data['order_id'] ?? $data['id'] ?? $data['reference'] ?? null;

        return SupplierResult::success($reference ? (string) $reference : null, $data, 'FAUSSMS accepted the order.');
    }

    public function orderStatus(Order $order): SupplierResult
    {
        $endpoint = config('faussms.endpoints.order_status');
        if (! $endpoint || ! $order->supplier_reference) {
            return SupplierResult::unavailable('FAUSSMS status endpoint or supplier reference is missing.');
        }

        $response = $this->request('GET', $endpoint.'?reference='.urlencode($order->supplier_reference), [], $order->id, 'order_status');
        if (! $response->successful()) {
            return SupplierResult::failed('FAUSSMS status request failed.', false, ['http_status' => $response->status()]);
        }

        return SupplierResult::success($order->supplier_reference, $response->json() ?? [], 'FAUSSMS status retrieved.');
    }

    public function catalog(): array
    {
        $endpoint = config('faussms.endpoints.catalog');
        if (! $endpoint) {
            return [];
        }

        $response = $this->request('GET', $endpoint, [], null, 'catalog');
        return $response->successful() ? ($response->json() ?? []) : [];
    }

    private function request(string $method, string $endpoint, array $payload, ?int $orderId, string $action = 'place_order')
    {
        $started = microtime(true);
        $response = Http::baseUrl(rtrim((string) config('faussms.base_url'), '/'))
            ->withToken((string) config('faussms.api_key'))
            ->acceptJson()
            ->timeout((int) config('faussms.timeout', 20))
            ->send($method, $endpoint, ['json' => $payload]);

        \App\Models\SupplierLog::create([
            'order_id' => $orderId,
            'supplier' => $this->name(),
            'adapter' => $this->name(),
            'action' => $action,
            'endpoint' => $endpoint,
            'request_payload' => $payload,
            'response_payload' => $response->json() ?? ['raw' => substr($response->body(), 0, 4000)],
            'http_status' => $response->status(),
            'status' => $response->successful() ? 'success' : 'failed',
            'error_message' => $response->successful() ? null : $response->body(),
            'duration_ms' => (int) ((microtime(true) - $started) * 1000),
        ]);

        return $response;
    }
}
