<?php

namespace App\Support;

use App\Models\Country;
use App\Models\Product;
use App\Services\Suppliers\SupplierManager;

class VerificationCatalogue
{
    public static function catalog(): array
    {
        $products = Product::active()
            ->where('type', 'virtual_number')
            ->orderBy('price')
            ->orderBy('name')
            ->get();

        $apps = [];
        $manager = app(SupplierManager::class);

        foreach ($products as $product) {
            $app = self::appFor($product);
            $countryCode = self::countryCode($product);

            if (! $app || $countryCode === '') {
                continue;
            }

            $providerReady = false;
            try {
                $providerReady = $manager->adapterFor($product)->supports($product);
            } catch (\Throwable) {
                $providerReady = false;
            }

            $slug = $app['slug'];
            $apps[$slug]['app'] = $app;
            $apps[$slug]['countries'][$countryCode] ??= self::country($countryCode);
            $apps[$slug]['countries'][$countryCode]['options'][] = [
                'id' => $product->id,
                'name' => $product->name,
                'summary' => $product->summary,
                'price' => (float) $product->price,
                'currency' => $product->currency,
                'price_decimals' => $product->priceDecimals(),
                'available' => $product->isAvailable() && $providerReady,
                'provider_ready' => $providerReady,
            ];
        }

        $catalog = [];
        foreach ($apps as $entry) {
            $app = $entry['app'];
            $countries = $entry['countries'];
            uasort($countries, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

            foreach ($countries as &$country) {
                usort($country['options'], fn ($a, $b) => $a['price'] <=> $b['price'] ?: strcasecmp($a['name'], $b['name']));
                $country['options'] = array_values($country['options']);
                $country['lowest_price'] = $country['options'][0]['price'] ?? null;
                $country['available_options'] = count(array_filter($country['options'], fn ($option) => $option['available']));
            }
            unset($country);

            $catalog[] = [
                'slug' => $app['slug'],
                'label' => $app['label'],
                'logo' => $app['logo'],
                'color' => $app['color'],
                'gradient' => $app['gradient'],
                'countries' => array_values($countries),
                'country_count' => count($countries),
                'option_count' => array_sum(array_map(fn ($country) => count($country['options']), $countries)),
            ];
        }

        usort($catalog, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return ['apps' => $catalog];
    }

    public static function selectionMatches(Product $product, string $app, string $country): bool
    {
        $selectedApp = self::appFor($product);

        return $selectedApp !== null
            && $selectedApp['slug'] === $app
            && self::countryCode($product) === strtoupper($country);
    }

    private static function appFor(Product $product): ?array
    {
        $haystack = strtolower(implode(' ', [
            $product->meta['app'] ?? '',
            $product->meta['service'] ?? '',
            $product->supplier_product_id ?? '',
            $product->name,
        ]));

        $map = [
            'whatsapp' => ['slug' => 'whatsapp', 'label' => 'WhatsApp', 'logo' => 'whatsapp', 'color' => '#25D366', 'gradient' => 'linear-gradient(135deg,#128C7E 0%,#25D366 100%)'],
            'facebook' => ['slug' => 'facebook', 'label' => 'Facebook', 'logo' => 'facebook', 'color' => '#1877F2', 'gradient' => 'linear-gradient(135deg,#1877F2 0%,#0A5BE0 100%)'],
            'tiktok' => ['slug' => 'tiktok', 'label' => 'TikTok', 'logo' => 'tiktok', 'color' => '#FE2C55', 'gradient' => 'linear-gradient(135deg,#010101 0%,#25F4EE 55%,#FE2C55 100%)'],
            'instagram' => ['slug' => 'instagram', 'label' => 'Instagram', 'logo' => 'instagram', 'color' => '#E1306C', 'gradient' => 'linear-gradient(135deg,#833AB4 0%,#E1306C 45%,#F56040 75%,#FCAF45 100%)'],
            'snapchat' => ['slug' => 'snapchat', 'label' => 'Snapchat', 'logo' => 'snapchat', 'color' => '#000000', 'gradient' => 'linear-gradient(135deg,#000000 0%,#1F2937 100%)'],
            'telegram' => ['slug' => 'telegram', 'label' => 'Telegram', 'logo' => 'telegram', 'color' => '#229ED9', 'gradient' => 'linear-gradient(135deg,#229ED9 0%,#1E96D4 100%)'],
            'twitter' => ['slug' => 'x-twitter', 'label' => 'X / Twitter', 'logo' => 'x', 'color' => '#000000', 'gradient' => 'linear-gradient(135deg,#1F2937 0%,#000000 100%)'],
        ];

        foreach ($map as $needle => $app) {
            if (str_contains($haystack, $needle)) {
                return $app;
            }
        }

        return null;
    }

    private static function countryCode(Product $product): string
    {
        $code = strtoupper(trim((string) ($product->meta['country'] ?? $product->meta['country_code'] ?? '')));

        return preg_match('/^[A-Z]{2}$/', $code) === 1 ? $code : '';
    }

    private static function country(string $code): array
    {
        $country = Country::where('code', $code)->first();

        return [
            'code' => $code,
            'name' => $country?->name ?? $code,
            'flag' => 'https://flagcdn.com/w80/'.strtolower($code).'.png',
            'options' => [],
        ];
    }
}
