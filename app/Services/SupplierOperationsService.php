<?php

namespace App\Services;

use App\Models\SupplierLog;
use App\Services\Suppliers\JustAnotherPanelCatalogImporter;
use App\Services\Suppliers\SupplierManager;
use App\Services\Suppliers\TextVerifiedCatalogImporter;
use Throwable;

class SupplierOperationsService
{
    public function __construct(
        private SupplierManager $suppliers,
        private SettingsService $settings,
    ) {}

    public function test(string $provider): array
    {
        $started = microtime(true);

        if ($provider === 'manual') {
            return $this->result($provider, 'failed', 'Manual fulfillment has no supplier API to test.');
        }

        $adapter = $this->suppliers->adapter($provider);
        $configured = method_exists($adapter, 'isConfigured') ? $adapter->isConfigured() : true;
        $enabled = (bool) $this->settings->get('supplier.'.$provider.'_enabled', true);

        if (! $configured || ! $enabled) {
            $this->log('connection.test', $provider, 'failed', 'Supplier is not configured or disabled.', null, ['provider' => $provider], $started);

            return $this->result($provider, 'failed', 'Supplier is not configured or disabled.');
        }

        try {
            if ($provider === 'herosms') {
                $catalog = $adapter->catalog();
                $count = count($catalog);
                $ok = $count > 0;
                $this->log('connection.test', $provider, $ok ? 'success' : 'failed', $ok ? 'HeroSMS catalogue request succeeded.' : 'HeroSMS catalogue returned no offers.', null, ['catalog_count' => $count], $started);

                return $this->result($provider, $ok ? 'connected' : 'failed', $ok ? 'HeroSMS API connection verified.' : 'HeroSMS API returned no catalogue offers.', null, null, $count);
            }

            $balance = method_exists($adapter, 'accountBalance') ? $adapter->accountBalance() : null;
            $ok = is_array($balance) && isset($balance['balance']) && is_numeric($balance['balance']);
            $this->log('connection.test', $provider, $ok ? 'success' : 'failed', $ok ? 'Supplier account request succeeded.' : 'Supplier account request did not return a valid balance.', null, ['balance' => $balance], $started);

            return $this->result(
                $provider,
                $ok ? 'connected' : 'failed',
                $ok ? 'Supplier API connection verified.' : 'Supplier account request failed or returned an invalid response.',
                $ok ? (float) $balance['balance'] : null,
                $ok ? strtoupper((string) ($balance['currency'] ?? 'USD')) : null,
                null
            );
        } catch (Throwable $e) {
            $this->log('connection.test', $provider, 'failed', 'Supplier request failed: '.$e->getMessage(), null, [], $started);

            return $this->result($provider, 'failed', 'Supplier request failed.');
        }
    }

    public function sync(string $provider): array
    {
        $started = microtime(true);

        try {
            $result = match ($provider) {
                'justanotherpanel' => $this->syncJustAnotherPanel(),
                'textverified' => $this->syncTextVerified(),
                'herosms' => $this->syncHeroSms(),
                default => ['error' => 'unsupported_supplier'],
            };
        } catch (Throwable $e) {
            $this->log('catalog.sync', $provider, 'failed', 'Supplier sync failed: '.$e->getMessage(), null, [], $started);

            return ['provider' => $provider, 'status' => 'failed', 'message' => 'Supplier sync failed.'];
        }

        $ok = ! isset($result['error']);
        $this->log('catalog.sync', $provider, $ok ? 'success' : 'failed', $ok ? 'Supplier service sync completed.' : (string) ($result['error'] ?? 'Supplier sync failed.'), null, $result, $started);

        return ['provider' => $provider] + $result + ['status' => $ok ? 'success' : 'failed'];
    }

    private function syncJustAnotherPanel(): array
    {
        $result = app(JustAnotherPanelCatalogImporter::class)->import([
            'brands' => ['instagram', 'tiktok', 'facebook', 'youtube', 'telegram', 'x-twitter', 'whatsapp'],
            'types' => 'Default',
            'markup' => (float) $this->settings->get('pricing.global_markup_percent', 10),
            'usd_xaf_rate' => 597.85,
            'status' => 'active',
            'limit' => (int) $this->settings->get('jap.service_limit', 0),
        ]);

        if (($result['catalog_count'] ?? 0) === 0) {
            return ['error' => 'catalog_unavailable', 'catalog_count' => 0];
        }

        return $result;
    }

    private function syncTextVerified(): array
    {
        return app(TextVerifiedCatalogImporter::class)->sync([
            'markup' => (float) $this->settings->get('pricing.global_markup_percent', 10),
            'usd_xaf_rate' => 597.85,
            'status' => 'active',
        ]);
    }

    private function syncHeroSms(): array
    {
        $catalog = $this->suppliers->adapter('herosms')->catalog();

        if ($catalog === []) {
            return ['error' => 'catalog_unavailable', 'catalog_count' => 0];
        }

        return [
            'catalog_count' => count($catalog),
            'imported' => 0,
            'matched' => 0,
            'error' => 'hero_import_mapping_not_configured',
        ];
    }

    private function result(string $provider, string $status, string $message, ?float $balance = null, ?string $currency = null, ?int $catalogCount = null): array
    {
        return [
            'provider' => $provider,
            'status' => $status,
            'message' => $message,
            'balance' => $balance,
            'balance_currency' => $currency,
            'catalog_count' => $catalogCount,
            'tested_at' => now()->toDateTimeString(),
        ];
    }

    private function log(string $action, string $provider, string $status, string $message, ?int $httpStatus, array $payload, float $started): void
    {
        SupplierLog::create([
            'supplier' => $provider,
            'adapter' => $provider,
            'action' => $action,
            'endpoint' => $action,
            'request_payload' => ['provider' => $provider],
            'response_payload' => $payload + ['message' => $message],
            'http_status' => $httpStatus,
            'status' => $status === 'failed' ? 'failed' : 'success',
            'error_message' => $status === 'failed' ? $message : null,
            'duration_ms' => (int) ((microtime(true) - $started) * 1000),
        ]);
    }
}
