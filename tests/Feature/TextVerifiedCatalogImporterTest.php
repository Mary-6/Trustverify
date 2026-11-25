<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Suppliers\TextVerifiedCatalogImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TextVerifiedCatalogImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_imports_priced_apps_and_archives_stale_products(): void
    {
        $this->configureTextVerified();
        $this->fakeTextVerified();

        Product::create([
            'name' => 'Facebook US SMS Verification',
            'type' => 'virtual_number',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'textverified',
            'supplier_adapter' => 'textverified',
            'supplier_product_id' => 'facebookv',
            'status' => 'active',
            'is_active' => true,
            'slug' => 'facebook-us-sms-verification',
        ]);

        $result = app(TextVerifiedCatalogImporter::class)->sync([
            'markup' => 10,
            'usd_xaf_rate' => 600,
            'status' => 'active',
        ]);

        $this->assertSame(7, $result['catalog_count']);
        $this->assertSame(7, $result['imported']);
        $this->assertSame(7, $result['matched']);
        $this->assertFalse($result['dry_run']);

        $products = Product::where('supplier_adapter', 'textverified')->where('type', 'virtual_number')->get();
        $this->assertSame(8, $products->count());
        $this->assertSame(7, $products->where('is_active', true)->count());

        $whatsapp = $products->firstWhere('supplier_product_id', 'whatsapp');
        $this->assertSame('WhatsApp US SMS Verification', $whatsapp->name);
        $this->assertSame(990.0, (float) $whatsapp->price);
        $this->assertSame(900.0, (float) $whatsapp->supplier_cost);
        $this->assertSame('US', $whatsapp->meta['country'] ?? null);
        $this->assertSame('whatsapp', $whatsapp->meta['textverified']['service_name'] ?? null);

        $facebook = $products->firstWhere('supplier_product_id', 'facebook');
        $this->assertSame('active', $facebook->status);
        $this->assertSame(true, (bool) $facebook->is_active);
        $this->assertSame(495.0, (float) $facebook->price);

        $stale = $products->firstWhere('supplier_product_id', 'facebookv');
        $this->assertSame('archived', $stale->status);
        $this->assertSame(false, (bool) $stale->is_active);

        $this->assertTrue($products->contains(fn ($product) => $product->supplier_product_id === 'telegram' && $product->is_active));
        $this->assertTrue($products->contains(fn ($product) => $product->supplier_product_id === 'x' && $product->is_active));
    }

    public function test_dry_run_reports_matches_without_writing_products(): void
    {
        $this->configureTextVerified();
        $this->fakeTextVerified();

        $result = app(TextVerifiedCatalogImporter::class)->sync(['dry_run' => true]);

        $this->assertSame(7, $result['catalog_count']);
        $this->assertSame(7, $result['imported']);
        $this->assertSame(7, $result['matched']);
        $this->assertTrue($result['dry_run']);
        $this->assertSame(0, Product::count());
    }

    private function configureTextVerified(): void
    {
        config()->set('textverified.enabled', true);
        config()->set('textverified.base_url', 'https://textverified.test');
        config()->set('textverified.api_key', 'tv-key');
        config()->set('textverified.api_username', 'tv-user');
    }

    private function fakeTextVerified(): void
    {
        $prices = [
            'whatsapp' => 1.50,
            'telegram' => 0.80,
            'instagram' => 0.50,
            'facebook' => 0.75,
            'tiktok' => 0.75,
            'x' => 0.55,
            'snapchat' => 0.25,
        ];

        Http::fake(function ($request) use ($prices) {
            $url = $request->url();

            if (str_contains($url, '/api/pub/v2/auth')) {
                return Http::response(['token' => 'tv-token', 'expiresAt' => now()->addHour()->toISOString()]);
            }

            if (str_contains($url, '/api/pub/v2/pricing/verifications')) {
                $payload = json_decode($request->body(), true) ?: [];
                $service = (string) ($payload['serviceName'] ?? '');

                return Http::response([
                    'serviceName' => $service,
                    'price' => $prices[$service] ?? null,
                ]);
            }

            if (str_contains($url, '/api/pub/v2/services')) {
                return Http::response(array_map(fn ($service) => [
                    'serviceName' => $service,
                    'capability' => 'Sms',
                    'description' => ucfirst($service),
                ], array_keys($prices)));
            }

            return Http::response([], 404);
        });
    }
}
