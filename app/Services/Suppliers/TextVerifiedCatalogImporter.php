<?php

namespace App\Services\Suppliers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class TextVerifiedCatalogImporter
{
    public function sync(array $options = []): array
    {
        $markup = (float) ($options['markup'] ?? 10);
        $usdXafRate = (float) ($options['usd_xaf_rate'] ?? 597.85);
        $statusOption = $options['status'] ?? 'active';
        $status = in_array($statusOption, ['active', 'draft', 'inactive', 'archived'], true) ? $statusOption : 'draft';
        $dryRun = (bool) ($options['dry_run'] ?? false);

        $adapter = app(TextVerifiedSupplierAdapter::class);
        if (! $adapter->isConfigured()) {
            return $this->result(0, 0, $dryRun, 'TextVerified is not configured.');
        }

        $catalog = $adapter->catalog();
        if ($catalog === []) {
            return $this->result(0, 0, $dryRun, 'TextVerified catalogue is unavailable.');
        }

        $offers = $this->offers($adapter, $catalog);
        $imported = 0;
        $category = null;

        if (! $dryRun && $offers !== []) {
            $category = Category::updateOrCreate(
                ['slug' => 'virtual-numbers-sms'],
                [
                    'name' => 'Virtual Numbers & SMS',
                    'type' => 'virtual_number',
                    'description' => 'SMS verification numbers for supported apps and services.',
                    'sort_order' => 3,
                    'is_active' => true,
                ]
            );
        }

        foreach ($offers as $offer) {
            $supplierPriceUsd = (float) $offer['supplier_price_usd'];
            $supplierCost = $this->convertUsd($supplierPriceUsd, 1, $usdXafRate);
            $price = $this->convertUsd($supplierPriceUsd, 1 + ($markup / 100), $usdXafRate);
            $name = $offer['label'].' US SMS Verification';

            if ($dryRun) {
                $imported++;
                continue;
            }

            $existing = Product::where('supplier_adapter', 'textverified')
                ->where('supplier_product_id', $offer['service_name'])
                ->first();

            $existingMeta = $existing?->meta ?? [];
            $adminDisabled = (bool) ($existingMeta['admin_disabled'] ?? false);
            $preserveDisabled = $adminDisabled || ($existing !== null && $existing->status !== 'archived' && (! $existing->is_active || $existing->status !== 'active'));

            Product::updateOrCreate(
                [
                    'supplier_adapter' => 'textverified',
                    'supplier_product_id' => $offer['service_name'],
                ],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => $this->slugFor($name, (string) $offer['service_name']),
                    'type' => 'virtual_number',
                    'summary' => 'US mobile number for '.$offer['label'].' (SMS).',
                    'description' => 'Receive a US mobile number for '.$offer['label'].'. One SMS verification per order. Codes are delivered to your order when received. US numbers only.',
                    'terms' => 'Verification codes are time-sensitive. Do not share codes with anyone. This product provides US numbers only.',
                    'price' => $existing?->price ?? $price,
                    'currency' => 'XAF',
                    'supplier_cost' => $supplierCost,
                    'supplier' => 'textverified',
                    'supplier_product_id' => $offer['service_name'],
                    'supplier_adapter' => 'textverified',
                    'meta' => [
                        'country' => 'US',
                        'pricing_unit' => 'per_unit',
                        'textverified' => [
                            'carrier' => false,
                            'area_code' => false,
                            'max_price' => $supplierPriceUsd,
                            'capability' => 'sms',
                            'number_type' => 'mobile',
                            'service_name' => $offer['service_name'],
                        ],
                        'usd_xaf_rate' => $usdXafRate,
                        'markup_percent' => $markup,
                        'supplier_price_usd' => $supplierPriceUsd,
                        'admin_disabled' => $adminDisabled,
                    ],
                    'status' => $preserveDisabled ? ($existing->status ?: 'draft') : $status,
                    'is_active' => ! $preserveDisabled,
                    'is_featured' => false,
                    'sort_order' => 0,
                    'stock_quantity' => null,
                    'min_order' => 1,
                    'max_order' => 1,
                ]
            );

            $imported++;
        }

        if (! $dryRun && $offers !== []) {
            $serviceNames = array_values(array_unique(array_column($offers, 'service_name')));

            Product::where('supplier_adapter', 'textverified')
                ->where('type', 'virtual_number')
                ->whereNotIn('supplier_product_id', $serviceNames)
                ->update([
                    'status' => 'archived',
                    'is_active' => false,
                ]);
        }

        return [
            'catalog_count' => count($catalog),
            'imported' => $imported,
            'matched' => count($offers),
            'dry_run' => $dryRun,
        ];
    }

    private function offers(TextVerifiedSupplierAdapter $adapter, array $catalog): array
    {
        $byService = [];
        foreach ($catalog as $service) {
            $name = strtolower((string) ($service['service_name'] ?? ''));
            if ($name !== '') {
                $byService[$name] = $service;
            }
        }

        $offers = [];
        $used = [];

        foreach ($this->apps() as $app) {
            foreach ($app['candidates'] as $candidate) {
                $service = $byService[strtolower($candidate)] ?? null;
                if (! $service || $this->catalogCapability($service['capability'] ?? null) !== 'sms') {
                    continue;
                }

                $pricing = $adapter->pricing((string) $service['service_name'], 'sms');
                $priceUsd = isset($pricing['price']) ? (float) $pricing['price'] : 0.0;
                $serviceName = (string) $service['service_name'];

                if ($priceUsd <= 0 || isset($used[$serviceName])) {
                    continue;
                }

                $used[$serviceName] = true;
                $offers[] = [
                    'label' => $app['label'],
                    'service_name' => $serviceName,
                    'supplier_price_usd' => $priceUsd,
                ];

                break;
            }
        }

        return $offers;
    }

    private function slugFor(string $name, string $serviceName): string
    {
        $slug = Str::slug($name);

        $conflict = Product::where('slug', $slug)
            ->where(function ($query) use ($serviceName) {
                $query->where('supplier_adapter', '!=', 'textverified')
                    ->orWhere('supplier_product_id', '!=', $serviceName)
                    ->orWhereNull('supplier_product_id');
            })
            ->exists();

        return $conflict ? Str::slug($name.' '.$serviceName) : $slug;
    }

    private function apps(): array
    {
        return [
            ['label' => 'WhatsApp', 'candidates' => ['whatsapp']],
            ['label' => 'Telegram', 'candidates' => ['telegram']],
            ['label' => 'Instagram', 'candidates' => ['instagram']],
            ['label' => 'Facebook', 'candidates' => ['facebook', 'facebookv']],
            ['label' => 'TikTok', 'candidates' => ['tiktok']],
            ['label' => 'X / Twitter', 'candidates' => ['x', 'twitter']],
            ['label' => 'Snapchat', 'candidates' => ['snapchat', 'snapchatv']],
        ];
    }

    private function catalogCapability(mixed $capability): ?string
    {
        $capability = strtolower((string) $capability);

        return str_contains($capability, 'sms') ? 'sms' : null;
    }

    private function result(int $catalogCount, int $imported, bool $dryRun, string $error): array
    {
        return [
            'catalog_count' => $catalogCount,
            'imported' => $imported,
            'matched' => 0,
            'dry_run' => $dryRun,
            'error' => $error,
        ];
    }

    private function convertUsd(float $usd, float $multiplier, float $usdXafRate): string
    {
        $usd = number_format($usd, 6, '.', '');
        $multiplier = number_format($multiplier, 6, '.', '');
        $usdXaf = number_format($usdXafRate, 6, '.', '');

        return bcadd(bcmul(bcmul($usd, $multiplier, 6), $usdXaf, 6), '0', 2);
    }
}
