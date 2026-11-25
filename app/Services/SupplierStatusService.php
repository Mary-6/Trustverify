<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierLog;
use App\Services\Suppliers\SupplierManager;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SupplierStatusService
{
    public function __construct(
        private SupplierManager $suppliers,
        private SettingsService $settings,
    ) {}

    public function providers(): array
    {
        return array_map(
            fn (array $definition) => $this->provider($definition['key'], $definition),
            $this->definitions()
        );
    }

    private function definitions(): array
    {
        return [
            ['key' => 'justanotherpanel', 'label' => 'JustAnotherPanel', 'type' => 'SMM', 'enabled_default' => true],
            ['key' => 'textverified', 'label' => 'TextVerified', 'type' => 'Verification', 'enabled_default' => true],
            ['key' => 'herosms', 'label' => 'HeroSMS', 'type' => 'Verification', 'enabled_default' => true],
            ['key' => 'faussms', 'label' => 'FAUSSMS', 'type' => 'SMM', 'enabled_default' => false],
            ['key' => 'manual', 'label' => 'Manual fulfillment', 'type' => 'Internal', 'enabled_default' => true],
        ];
    }

    private function provider(string $key, array $definition): array
    {
        $adapter = $this->suppliers->adapter($key);
        $configured = method_exists($adapter, 'isConfigured') ? $adapter->isConfigured() : true;
        $enabled = (bool) $this->settings->get('supplier.'.$key.'_enabled', $definition['enabled_default']);
        $active = $configured && $enabled;

        $balance = null;
        $balanceCurrency = null;
        $balanceStatus = 'unavailable';

        if ($active && method_exists($adapter, 'accountBalance')) {
            $cached = Cache::remember('supplier-status:balance:'.$key, now()->addMinutes(5), function () use ($adapter) {
                try {
                    return $adapter->accountBalance();
                } catch (Throwable) {
                    return null;
                }
            });

            if (is_array($cached) && isset($cached['balance']) && is_numeric($cached['balance'])) {
                $balance = (float) $cached['balance'];
                $balanceCurrency = strtoupper((string) ($cached['currency'] ?? 'USD'));
                $balanceStatus = 'ok';
            }
        }

        $log = SupplierLog::query()
            ->where(fn ($query) => $query->where('supplier', $key)->orWhere('adapter', $key))
            ->latest()
            ->first();

        return [
            'key' => $key,
            'label' => $definition['label'],
            'type' => $definition['type'],
            'configured' => $configured,
            'enabled' => $enabled,
            'active' => $active,
            'status' => ! $configured ? 'not_configured' : (! $enabled ? 'disabled' : 'active'),
            'balance' => $balance,
            'balance_currency' => $balanceCurrency,
            'balance_status' => $balanceStatus,
            'services' => Product::where(fn ($query) => $query->where('supplier_adapter', $key)->orWhere('supplier', $key))->where('is_active', true)->count(),
            'orders' => Order::whereHas('product', fn ($query) => $query->where('supplier_adapter', $key)->orWhere('supplier', $key))->count(),
            'last_request' => $log ? [
                'action' => $log->action,
                'endpoint' => $log->endpoint,
                'http_status' => $log->http_status,
                'status' => $log->status,
                'duration_ms' => $log->duration_ms,
                'created_at' => $log->created_at?->toDateTimeString(),
                'ago' => $log->created_at?->diffForHumans(),
            ] : null,
        ];
    }
}
