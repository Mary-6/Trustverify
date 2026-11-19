<?php

namespace App\Services\Suppliers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TextVerifiedSupplierAdapter implements SupplierAdapter
{
    private const USER_AGENT = 'TrustVerifySMS/1.0 (+https://trustverifysms.com)';

    public function name(): string
    {
        return 'textverified';
    }

    public function supports(Product $product): bool
    {
        $forTextVerified = ($product->supplier === 'textverified' || $product->supplier_adapter === 'textverified')
            && $product->type === 'virtual_number';

        return $forTextVerified && $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return filter_var(config('textverified.enabled'), FILTER_VALIDATE_BOOLEAN)
            && ! empty(config('textverified.api_key'))
            && ! empty(config('textverified.api_username'))
            && ! empty(config('textverified.base_url'));
    }

    public function placeOrder(Order $order): SupplierResult
    {
        $product = $order->product;
        $config = $this->productConfig($product);

        $serviceName = $config['service_name'] ?? $product?->supplier_product_id;
        $capability = $this->capability($config['capability'] ?? 'sms');

        if (! $serviceName) {
            return SupplierResult::failed('TextVerified service name is not configured for this product.', true);
        }

        if (! $capability) {
            return SupplierResult::failed('TextVerified capability must be sms, voice, or smsAndVoiceCombo.', true);
        }

        $payload = array_filter([
            'serviceName' => (string) $serviceName,
            'capability' => $capability,
            'areaCodeSelectOption' => $this->list($config['area_codes'] ?? null),
            'carrierSelectOption' => $this->list($config['carriers'] ?? null),
            'serviceNotListedName' => $config['service_not_listed_name'] ?? null,
            'maxPrice' => isset($config['max_price']) && is_numeric($config['max_price']) ? (float) $config['max_price'] : null,
        ], fn ($value) => $value !== null && $value !== []);

        $response = $this->request('POST', '/api/pub/v2/verifications', $payload, [], $order->id, 'verification.create');
        if (! $response->successful()) {
            return SupplierResult::failed('TextVerified verification request failed.', $response->clientError(), ['http_status' => $response->status()]);
        }

        $created = $response->json() ?? [];
        $details = $this->followVerification($created, $response, $order->id);

        if (! $details || ! $details->successful()) {
            return SupplierResult::failed('TextVerified verification could not be retrieved.', false, ['http_status' => $details?->status()]);
        }

        $verification = $details->json() ?? [];
        $reference = $verification['id'] ?? $created['id'] ?? null;

        if (! $reference) {
            return SupplierResult::failed('TextVerified response did not include a verification reference.', false);
        }

        $state = (string) ($verification['state'] ?? 'verificationPending');

        return SupplierResult::success((string) $reference, [
            'status' => $this->mapState($state),
            'supplier_status' => $state,
            'state' => $state,
            'delivery' => $this->delivery($verification, $serviceName),
        ], 'TextVerified verification created.');
    }

    public function orderStatus(Order $order): SupplierResult
    {
        if (! $order->supplier_reference) {
            return SupplierResult::unavailable('TextVerified supplier reference is missing.');
        }

        $response = $this->request('GET', '/api/pub/v2/verifications/'.rawurlencode($order->supplier_reference), [], [], $order->id, 'verification.status');
        if (! $response->successful()) {
            return SupplierResult::failed('TextVerified status request failed.', false, ['http_status' => $response->status()]);
        }

        $verification = $response->json() ?? [];
        $state = (string) ($verification['state'] ?? '');

        return SupplierResult::success($order->supplier_reference, [
            'status' => $this->mapState($state),
            'supplier_status' => $state,
            'state' => $state,
            'delivery' => $this->delivery($verification, $verification['serviceName'] ?? null),
        ], 'TextVerified status retrieved.');
    }

    public function catalog(): array
    {
        try {
            $response = $this->request('GET', '/api/pub/v2/services', [], ['numberType' => 'mobile', 'reservationType' => 'verification'], null, 'catalog');
        } catch (\Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json() ?? [])->map(fn ($service) => [
            'service_name' => $service['serviceName'] ?? null,
            'capability' => $service['capability'] ?? null,
            'description' => $service['description'] ?? null,
        ])->filter(fn ($service) => $service['service_name'])->values()->all();
    }

    private function bearerToken(): string
    {
        $username = (string) config('textverified.api_username');
        $cacheKey = 'supplier:textverified:token:'.md5($username.'|'.config('textverified.api_key'));

        if ($token = Cache::get($cacheKey)) {
            return (string) $token;
        }

        $response = Http::baseUrl(rtrim((string) config('textverified.base_url'), '/'))
            ->withHeaders([
                'X-API-KEY' => (string) config('textverified.api_key'),
                'X-API-USERNAME' => $username,
                'User-Agent' => self::USER_AGENT,
            ])
            ->acceptJson()
            ->timeout((int) config('textverified.timeout', 30))
            ->post('/api/pub/v2/auth', new \stdClass);

        if (! $response->successful()) {
            throw new RuntimeException('TextVerified authentication failed with HTTP '.$response->status().'.');
        }

        $data = $response->json() ?? [];
        $token = $data['token'] ?? null;

        if (! $token) {
            throw new RuntimeException('TextVerified authentication response did not include a token.');
        }

        $ttl = now()->addMinutes(50);
        if (! empty($data['expiresAt'])) {
            try {
                $expiresAt = Carbon::parse($data['expiresAt'])->subMinute();
                if ($expiresAt->isFuture()) {
                    $ttl = $expiresAt;
                }
            } catch (\Throwable) {
            }
        }

        Cache::put($cacheKey, (string) $token, $ttl);

        return (string) $token;
    }

    private function request(string $method, string $endpoint, array $payload = [], array $query = [], ?int $orderId = null, string $action = 'request')
    {
        $started = microtime(true);
        $url = $this->url($endpoint);
        $http = Http::withToken($this->bearerToken())
            ->withHeaders(['User-Agent' => self::USER_AGENT])
            ->acceptJson()
            ->timeout((int) config('textverified.timeout', 30));

        $response = $method === 'GET'
            ? $http->get($url, $query)
            : $http->send($method, $url, ['json' => $payload]);

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

    private function followVerification(array $created, $createResponse, int $orderId)
    {
        $action = $created['data'] ?? $created;
        $href = $action['href'] ?? ($createResponse->header('Location') ?: null);
        $method = strtoupper((string) ($action['method'] ?? 'GET'));

        if (! $href) {
            $id = $created['id'] ?? $created['verificationId'] ?? null;
            if (! $id) {
                return null;
            }

            $href = '/api/pub/v2/verifications/'.rawurlencode((string) $id);
            $method = 'GET';
        }

        return $this->request($method === 'POST' ? 'POST' : 'GET', $href, [], [], $orderId, 'verification.details');
    }

    private function url(string $endpoint): string
    {
        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        return rtrim((string) config('textverified.base_url'), '/').'/'.ltrim($endpoint, '/');
    }

    private function productConfig(?Product $product): array
    {
        $meta = $product?->meta ?? [];

        return $meta['textverified'] ?? $meta;
    }

    private function capability(mixed $value): ?string
    {
        return match (strtolower((string) $value)) {
            'sms' => 'sms',
            'voice' => 'voice',
            'smsandvoicecombo', 'sms_and_voice', 'sms+voice', 'both' => 'smsAndVoiceCombo',
            default => null,
        };
    }

    private function list(mixed $value): ?array
    {
        if (is_array($value)) {
            $value = array_filter($value);
        } elseif (is_string($value) && trim($value) !== '') {
            $value = preg_split('/[\s,]+/', trim($value)) ?: [];
        }

        $value = array_values(array_filter((array) $value));

        return $value ?: null;
    }

    private function mapState(string $state): string
    {
        $state = strtolower($state);

        return match (true) {
            str_contains($state, 'completed'), str_contains($state, 'reused') => 'completed',
            str_contains($state, 'canceled'), str_contains($state, 'cancelled') => 'cancelled',
            str_contains($state, 'timedout'), str_contains($state, 'timed_out'), str_contains($state, 'refunded'), str_contains($state, 'failed') => 'failed',
            default => 'processing',
        };
    }

    private function delivery(array $verification, mixed $serviceName): array
    {
        return array_filter([
            'number' => $verification['number'] ?? null,
            'service_name' => $verification['serviceName'] ?? $serviceName,
            'state' => $verification['state'] ?? null,
            'ends_at' => $verification['endsAt'] ?? null,
            'total_cost' => $verification['totalCost'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
