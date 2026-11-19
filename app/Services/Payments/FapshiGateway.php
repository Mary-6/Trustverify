<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FapshiGateway
{
    public function isConfigured(): bool
    {
        return (bool) config('fapshi.enabled')
            && filled(config('fapshi.api_user'))
            && filled(config('fapshi.api_key'));
    }

    public function initiatePay(int $amount, string $userId, string $externalId, ?string $email = null, ?string $redirectUrl = null, ?string $message = null): array
    {
        $payload = [
            'amount' => $amount,
            'userId' => $userId,
            'externalId' => $externalId,
        ];

        if ($email) {
            $payload['email'] = $email;
        }
        if ($redirectUrl) {
            $payload['redirectUrl'] = $redirectUrl;
        }
        if ($message) {
            $payload['message'] = $message;
        }

        $response = Http::withHeaders($this->headers())
            ->timeout((int) config('fapshi.timeout', 20))
            ->post($this->url('/initiate-pay'), $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Fapshi initiate-pay failed: '.($response->json('message') ?? $response->body()));
        }

        $data = $response->json();

        if (empty($data['link']) || empty($data['transId'])) {
            throw new RuntimeException('Fapshi initiate-pay returned an incomplete response.');
        }

        return $data;
    }

    public function paymentStatus(string $transId): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout((int) config('fapshi.timeout', 20))
            ->get($this->url('/payment-status/'.$transId));

        if (! $response->successful()) {
            throw new RuntimeException('Fapshi payment-status failed: '.($response->json('message') ?? $response->body()));
        }

        return $response->json();
    }

    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'apiuser' => (string) config('fapshi.api_user'),
            'apikey' => (string) config('fapshi.api_key'),
        ];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('fapshi.base_url', 'https://sandbox.fapshi.com'), '/').$path;
    }
}
