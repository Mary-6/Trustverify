<?php

namespace App\Services\Suppliers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class JustAnotherPanelCatalogImporter
{
    public function __construct(private JustAnotherPanelSupplierAdapter $adapter) {}

    public function import(array $options = []): array
    {
        $brands = $this->requestedBrands((array) ($options['brands'] ?? []));
        $types = array_values(array_filter(array_map('trim', explode(',', (string) ($options['types'] ?? 'Default')))));
        $markup = (float) ($options['markup'] ?? 10);
        $usdXafRate = (float) ($options['usd_xaf_rate'] ?? 597.85);
        $statusOption = $options['status'] ?? 'active';
        $status = in_array($statusOption, ['active', 'draft', 'inactive', 'archived'], true) ? $statusOption : 'draft';
        $limit = max(0, (int) ($options['limit'] ?? 0));
        $dryRun = (bool) ($options['dry_run'] ?? false);

        $catalog = $this->adapter->catalog();
        $matched = 0;
        $imported = 0;
        $matchedByBrand = [];
        $samples = [];
        $categories = [];

        foreach ($catalog as $service) {
            $serviceId = $service['service'] ?? null;
            $supplierName = trim((string) ($service['name'] ?? ''));
            $rate = (float) ($service['rate'] ?? 0);
            $type = (string) ($service['type'] ?? 'Default');

            if (! $serviceId || $supplierName === '' || $rate <= 0) {
                continue;
            }

            if ($types !== [] && ! in_array($type, $types, true)) {
                continue;
            }

            $brandKey = $this->brandFor($supplierName, $brands);
            if (! $brandKey) {
                continue;
            }

            $brand = $brands[$brandKey];
            $matched++;
            $matchedByBrand[$brandKey] = ($matchedByBrand[$brandKey] ?? 0) + 1;

            $displayName = $this->cleanName($supplierName);
            if ($displayName === '') {
                $displayName = 'JustAnotherPanel Service '.$serviceId;
            }

            $price = $this->convertRate($rate, 1 + ($markup / 100), $usdXafRate);
            $supplierCost = $this->convertRate($rate, 1, $usdXafRate);
            if (bccomp($price, '0', 2) <= 0) {
                continue;
            }

            $min = max(1, (int) ($service['min'] ?? 1));
            $max = (int) ($service['max'] ?? 0);

            $sample = [
                'service_id' => (string) $serviceId,
                'brand' => $brandKey,
                'name' => Str::limit($displayName, 120, ''),
                'price_xaf_per_1000' => $price,
                'supplier_cost_xaf_per_1000' => $supplierCost,
                'min' => $min,
                'max' => $max > 0 ? $max : null,
            ];

            if ($limit > 0 && $imported >= $limit) {
                if (count($samples) < 10) {
                    $samples[] = $sample;
                }
                continue;
            }

            if (count($samples) < 10) {
                $samples[] = $sample;
            }

            if ($dryRun) {
                $imported++;
                continue;
            }

            $category = $categories[$brandKey] ??= Category::updateOrCreate(
                ['slug' => $brand['category_slug']],
                [
                    'name' => $brand['label'],
                    'type' => 'smm_service',
                    'description' => 'JustAnotherPanel '.$brand['label'].' SMM services.',
                    'sort_order' => $brand['sort_order'],
                    'is_active' => true,
                ]
            );

            $existing = Product::where('supplier_adapter', 'justanotherpanel')
                ->where('supplier_product_id', (string) $serviceId)
                ->first();

            Product::updateOrCreate(
                [
                    'supplier_adapter' => 'justanotherpanel',
                    'supplier_product_id' => (string) $serviceId,
                ],
                [
                    'category_id' => $category->id,
                    'type' => 'smm_service',
                    'name' => Str::limit($displayName, 180, ''),
                    'summary' => Str::limit($displayName, 500, ''),
                    'description' => $this->description($service, $brand, $rate, $usdXafRate, $markup),
                    'terms' => 'Delivery time, refill eligibility and cancellation rules are controlled by the JustAnotherPanel service configuration.',
                    'price' => $existing?->price ?? $price,
                    'currency' => 'XAF',
                    'supplier_cost' => $supplierCost,
                    'supplier' => 'justanotherpanel',
                    'supplier_product_id' => (string) $serviceId,
                    'supplier_adapter' => 'justanotherpanel',
                    'meta' => [
                        'pricing_unit' => 'per_1000',
                        'supplier_service_name' => $supplierName,
                        'supplier_category' => (string) ($service['category'] ?? ''),
                        'supplier_type' => $type,
                        'supplier_rate_usd' => $rate,
                        'usd_xaf_rate' => $usdXafRate,
                        'markup_percent' => $markup,
                        'dripfeed' => (bool) ($service['dripfeed'] ?? false),
                        'refill' => (bool) ($service['refill'] ?? false),
                        'cancel' => (bool) ($service['cancel'] ?? false),
                    ],
                    'status' => $status,
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => 0,
                    'stock_quantity' => null,
                    'min_order' => $min,
                    'max_order' => $max > 0 ? $max : null,
                ]
            );

            $imported++;
        }

        return [
            'catalog_count' => count($catalog),
            'matched' => $matched,
            'imported' => $imported,
            'matched_by_brand' => $matchedByBrand,
            'samples' => $samples,
            'dry_run' => $dryRun,
        ];
    }

    public function sync(array $options = []): array
    {
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $catalog = $this->adapter->catalog();

        if ($catalog === []) {
            return [
                'catalog_count' => 0,
                'products' => 0,
                'missing' => 0,
                'candidates' => 0,
                'archived' => 0,
                'dry_run' => $dryRun,
                'error' => 'catalog_unavailable',
            ];
        }

        $availableIds = collect($catalog)
            ->map(fn ($service) => (string) ($service['service'] ?? ''))
            ->filter()
            ->flip();

        $products = Product::query()
            ->where('supplier_adapter', 'justanotherpanel')
            ->whereNotNull('supplier_product_id')
            ->get(['id', 'supplier_product_id', 'status', 'is_active']);

        $missing = $products->reject(fn (Product $product) => $availableIds->has((string) $product->supplier_product_id));
        $toArchive = $missing->filter(fn (Product $product) => $product->is_active || $product->status !== 'archived');

        if (! $dryRun && $toArchive->isNotEmpty()) {
            Product::query()
                ->whereIn('id', $toArchive->pluck('id'))
                ->update(['is_active' => false, 'status' => 'archived']);
        }

        return [
            'catalog_count' => count($catalog),
            'products' => $products->count(),
            'missing' => $missing->count(),
            'candidates' => $toArchive->count(),
            'archived' => $dryRun ? 0 : $toArchive->count(),
            'dry_run' => $dryRun,
        ];
    }

    private function brands(): array
    {
        return [
            'instagram' => ['label' => 'Instagram', 'category_slug' => 'instagram', 'sort_order' => 1, 'needles' => ['instagram']],
            'tiktok' => ['label' => 'TikTok', 'category_slug' => 'tiktok', 'sort_order' => 2, 'needles' => ['tiktok']],
            'facebook' => ['label' => 'Facebook', 'category_slug' => 'facebook', 'sort_order' => 3, 'needles' => ['facebook']],
            'youtube' => ['label' => 'YouTube', 'category_slug' => 'youtube', 'sort_order' => 4, 'needles' => ['youtube']],
            'telegram' => ['label' => 'Telegram', 'category_slug' => 'telegram', 'sort_order' => 5, 'needles' => ['telegram']],
            'x-twitter' => ['label' => 'X / Twitter', 'category_slug' => 'x-twitter', 'sort_order' => 6, 'needles' => ['twitter', 'x - twitter']],
            'snapchat' => ['label' => 'Snapchat', 'category_slug' => 'snapchat', 'sort_order' => 7, 'needles' => ['snapchat']],
            'whatsapp' => ['label' => 'WhatsApp', 'category_slug' => 'whatsapp', 'sort_order' => 8, 'needles' => ['whatsapp']],
        ];
    }

    private function requestedBrands(array $input): array
    {
        $brands = $this->brands();
        $input = array_values(array_filter(array_map(fn ($brand) => Str::lower(trim((string) $brand)), $input)));

        if ($input === []) {
            return $brands;
        }

        $aliases = [];
        foreach ($input as $value) {
            $aliases[] = Str::slug($value);
            if (in_array($value, ['twitter', 'x'], true)) {
                $aliases[] = 'x-twitter';
            }
        }

        return array_filter($brands, function ($brand, $key) use ($aliases) {
            return in_array($key, $aliases, true) || in_array($brand['category_slug'], $aliases, true) || in_array(Str::slug($brand['label']), $aliases, true);
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function brandFor(string $name, array $brands): ?string
    {
        $haystack = Str::lower($name);
        foreach ($brands as $key => $brand) {
            foreach ($brand['needles'] as $needle) {
                if (str_contains($haystack, Str::lower($needle))) {
                    return $key;
                }
            }
        }

        return null;
    }

    private function cleanName(string $name): string
    {
        $clean = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}]/u', ' ', $name) ?? $name;
        $clean = preg_replace('/\s+/', ' ', $clean) ?? $clean;

        return trim($clean);
    }

    private function convertRate(float $rateUsd, float $multiplier, float $usdXafRate): string
    {
        $rate = number_format($rateUsd, 6, '.', '');
        $multiplier = number_format($multiplier, 6, '.', '');
        $usdXaf = number_format($usdXafRate, 6, '.', '');

        return bcadd(bcmul(bcmul($rate, $multiplier, 6), $usdXaf, 6), '0', 2);
    }

    private function description(array $service, array $brand, float $rate, float $usdXafRate, float $markup): string
    {
        $lines = [
            'Imported from JustAnotherPanel service '.$service['service'].'.',
            'Brand: '.$brand['label'].'.',
            'Supplier category: '.($service['category'] ?? 'Uncategorized').'.',
            'Service type: '.($service['type'] ?? 'Default').'.',
            'Supplier rate: '.number_format($rate, 4).' USD per 1,000.',
            'Retail basis: +'.number_format($markup, 2).'% over supplier rate at '.number_format($usdXafRate, 2).' XAF/USD.',
            'Minimum order: '.($service['min'] ?? 1).'.',
            'Maximum order: '.(($service['max'] ?? 0) > 0 ? $service['max'] : 'Unlimited').'.',
            'Drip-feed: '.(! empty($service['dripfeed']) ? 'yes' : 'no').'.',
            'Refill: '.(! empty($service['refill']) ? 'yes' : 'no').'.',
            'Cancel: '.(! empty($service['cancel']) ? 'yes' : 'no').'.',
        ];

        return implode("\n", $lines);
    }
}
