<?php

namespace App\Services\Suppliers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierLog;
use Illuminate\Support\Facades\Http;

class HeroSmsSupplierAdapter implements SupplierAdapter
{
    public function name(): string
    {
        return 'herosms';
    }

    public function supports(Product $product): bool
    {
        $forHeroSms = ($product->supplier === 'herosms' || $product->supplier_adapter === 'herosms')
            && $product->type === 'virtual_number';

        return $forHeroSms && $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return filter_var(config('herosms.enabled'), FILTER_VALIDATE_BOOLEAN)
            && ! empty(config('herosms.api_key'))
            && ! empty(config('herosms.base_url'));
    }

    public function placeOrder(Order $order): SupplierResult
    {
        if ((int) $order->quantity !== 1) {
            return SupplierResult::failed('HeroSMS adapter supports one number per order.', true);
        }

        $product = $order->product;
        $config = $this->productConfig($product);
        $service = $config['service'] ?? $config['service_name'] ?? $product?->supplier_product_id ?? $order->payload['service'] ?? null;
        $country = $config['country_id'] ?? $config['country'] ?? null;

        if (! $service || ! $country) {
            return SupplierResult::failed('HeroSMS service and country mapping are required for this product.', true);
        }

        $payload = array_filter([
            'service' => (string) $service,
            'country' => (string) $country,
            'amount' => 1,
            'verificationType' => 'sms',
            'maxPrice' => isset($config['max_price']) && is_numeric($config['max_price']) ? (float) $config['max_price'] : null,
        ], fn ($value) => $value !== null);

        $response = $this->request('POST', '/activations', $payload, [], $order->id, 'activation.create');
        if (! $response->successful()) {
            return SupplierResult::failed('HeroSMS activation request failed.', $response->clientError(), ['http_status' => $response->status()]);
        }

        $data = $response->json() ?? [];
        $activation = null;

        if (isset($data['data']) && is_array($data['data'])) {
            $activation = isset($data['data']['id']) ? $data['data'] : ($data['data'][0] ?? null);
        } elseif (isset($data['id'])) {
            $activation = $data;
        }

        if (! is_array($activation) || empty($activation['id'])) {
            return SupplierResult::failed('HeroSMS activation response did not include an activation id.', false);
        }

        return SupplierResult::success((string) $activation['id'], [
            'status' => 'processing',
            'supplier_status' => $activation['status'] ?? null,
            'state' => $activation['status'] ?? 'WAIT',
            'delivery' => $this->delivery($activation, $service, $country),
        ], 'HeroSMS activation created.');
    }

    public function orderStatus(Order $order): SupplierResult
    {
        if (! $order->supplier_reference) {
            return SupplierResult::unavailable('HeroSMS supplier reference is missing.');
        }

        $response = $this->request('GET', '/activations/'.rawurlencode($order->supplier_reference).'/otp/last', [], [], $order->id, 'activation.otp_last');

        if ($response->status() === 404 || $response->status() === 400) {
            $title = (string) (($response->json() ?? [])['title'] ?? '');
            if (str_contains(strtoupper((string) $title), 'NOT_ACTIVE')) {
                return SupplierResult::success($order->supplier_reference, [
                    'status' => 'failed',
                    'supplier_status' => $title,
                    'state' => $title,
                ], 'HeroSMS activation is no longer active.');
            }

            return SupplierResult::success($order->supplier_reference, [
                'status' => 'processing',
                'supplier_status' => 'WAITING_SMS',
                'state' => 'WAITING_SMS',
            ], 'HeroSMS is waiting for SMS.');
        }

        if (! $response->successful()) {
            return SupplierResult::failed('HeroSMS status request failed.', false, ['http_status' => $response->status()]);
        }

        $payload = $response->json() ?? [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $smsCode = $data['smsCode'] ?? null;
        $smsText = $data['smsText'] ?? null;

        if (! $smsCode && ! $smsText) {
            return SupplierResult::success($order->supplier_reference, [
                'status' => 'processing',
                'supplier_status' => 'WAITING_SMS',
                'state' => 'WAITING_SMS',
            ], 'HeroSMS is waiting for SMS.');
        }

        $finish = $this->request('POST', '/activations/'.rawurlencode($order->supplier_reference).'/finish', [], [], $order->id, 'activation.finish');

        return SupplierResult::success($order->supplier_reference, [
            'status' => 'completed',
            'supplier_status' => 'SMS_RECEIVED',
            'state' => 'SMS_RECEIVED',
            'delivery' => array_filter([
                'sms_code' => $smsCode,
                'sms_text' => $smsText,
                'phone_from' => $data['from'] ?? null,
                'finish_status' => ($finish->json() ?? [])['status'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''),
        ], 'HeroSMS SMS received.');
    }

    public function catalog(): array
    {
        try {
            $response = $this->request('GET', '/activations/offers/sms', [], [], null, 'catalog');
        } catch (\Throwable) {
            return [];
        }

        return $response->successful() ? ($response->json() ?? []) : [];
    }

    private function request(string $method, string $endpoint, array $payload = [], array $query = [], ?int $orderId = null, string $action = 'request')
    {
        $started = microtime(true);
        $url = $this->url($endpoint);
        $response = Http::withHeaders([
                'Authorization' => 'ApiKey '.(string) config('herosms.api_key'),
                'Accept' => 'application/json',
            ])
            ->timeout((int) config('herosms.timeout', 30))
            ->send($method, $url, $method === 'GET' ? ['query' => $query] : ['json' => $payload]);

        SupplierLog::create([
            'order_id' => $orderId,
            'supplier' => $this->name(),
            'adapter' => $this->name(),
            'action' => $action,
            'endpoint' => $endpoint,
            'request_payload' => $payload + $query,
            'response_payload' => $response->json() ?? ['raw' => substr($response->body(), 0, 4000)],
            'http_status' => $response->status(),
            'status' => $response->successful() ? 'success' : 'failed',
            'error_message' => $response->successful() ? null : substr($response->body(), 0, 1000),
            'duration_ms' => (int) ((microtime(true) - $started) * 1000),
        ]);

        return $response;
    }

    private function url(string $endpoint): string
    {
        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        return rtrim((string) config('herosms.base_url'), '/').'/'.ltrim($endpoint, '/');
    }

    private function productConfig(?Product $product): array
    {
        $meta = $product?->meta ?? [];

        return $meta['herosms'] ?? $meta;
    }

    private function delivery(array $activation, mixed $service, mixed $country): array
    {
        return array_filter([
            'phone_number' => $activation['phone'] ?? null,
            'activation_id' => $activation['id'] ?? null,
            'service' => $service,
            'country_id' => $country,
            'expired_at' => $activation['expiredAt'] ?? $activation['expirationDate'] ?? null,
            'activation_cost' => $activation['activationCost'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
