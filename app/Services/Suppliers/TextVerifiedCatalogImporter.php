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
        $status = in_array(($options['status'] ?? 'active'), ['active', 'draft', 'inactive', 'archived'], true) ? $options['status'] : 'draft';
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $offers = $this->offers();
        $imported = 0;
        $category = null;

        if (! $dryRun) {
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
            $supplierCost = number_format(round(((float) $offer['supplier_price_usd']) * $usdXafRate, 2), 2, '.', '');

            if ($dryRun) {
                $imported++;
                continue;
            }

            Product::updateOrCreate(
                [
                    'supplier_adapter' => 'textverified',
                    'supplier_product_id' => $offer['service_name'],
                ],
                [
                    'category_id' => $category->id,
                    'name' => $offer['name'],
                    'slug' => Str::slug($offer['name']),
                    'type' => 'virtual_number',
                    'summary' => 'US mobile number for '.$offer['name'].' (SMS).',
                    'description' => 'Receive a US mobile number for '.$offer['name'].'. One SMS verification per order. Codes are delivered to your order when received. US numbers only.',
                    'terms' => 'Verification codes are time-sensitive. Do not share codes with anyone. This product provides US numbers only.',
                    'price' => $offer['price'],
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
                            'max_price' => $offer['supplier_price_usd'],
                            'capability' => 'sms',
                            'number_type' => 'mobile',
                            'service_name' => $offer['service_name'],
                        ],
                        'usd_xaf_rate' => $usdXafRate,
                        'markup_percent' => $markup,
                        'supplier_price_usd' => $offer['supplier_price_usd'],
                    ],
                    'status' => $status,
                    'is_active' => $status === 'active',
                    'is_featured' => false,
                    'sort_order' => 0,
                    'stock_quantity' => null,
                    'min_order' => 1,
                    'max_order' => 1,
                ]
            );

            $imported++;
        }

        return [
            'catalog_count' => count($offers),
            'imported' => $imported,
            'dry_run' => $dryRun,
        ];
    }

    private function offers(): array
    {
        return [
            ['name' => 'WhatsApp US SMS Verification', 'service_name' => 'whatsapp', 'supplier_price_usd' => 1.50, 'price' => '986.00'],
            ['name' => 'Facebook US SMS Verification', 'service_name' => 'facebookv', 'supplier_price_usd' => 0.75, 'price' => '493.00'],
            ['name' => 'TikTok US SMS Verification', 'service_name' => 'tiktok', 'supplier_price_usd' => 0.75, 'price' => '493.00'],
            ['name' => 'Instagram US SMS Verification', 'service_name' => 'instagram', 'supplier_price_usd' => 0.50, 'price' => '329.00'],
            ['name' => 'Snapchat US SMS Verification', 'service_name' => 'snapchatv', 'supplier_price_usd' => 0.25, 'price' => '164.00'],
        ];
    }
}
