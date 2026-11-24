<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierLog;
use App\Models\User;
use App\Services\Suppliers\HeroSmsSupplierAdapter;
use App\Services\Suppliers\JustAnotherPanelCatalogImporter;
use App\Services\Suppliers\JustAnotherPanelSupplierAdapter;
use App\Services\Suppliers\SupplierManager;
use App\Services\Suppliers\TextVerifiedSupplierAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupplierAdaptersTest extends TestCase
{
    use RefreshDatabase;

    public function test_textverified_places_and_checks_verification(): void
    {
        config()->set('textverified.enabled', true);
        config()->set('textverified.base_url', 'https://textverified.test');
        config()->set('textverified.api_key', 'tv-key');
        config()->set('textverified.api_username', 'tv-user');

        $product = $this->textVerifiedProduct();
        $order = $this->orderFor($product);
        $completed = false;

        $this->assertInstanceOf(TextVerifiedSupplierAdapter::class, app(SupplierManager::class)->adapterFor($product));
        $this->assertTrue(app(SupplierManager::class)->adapterFor($product)->supports($product));

        Http::fake(function ($request) use (&$completed) {
            $url = $request->url();

            if (str_contains($url, '/api/pub/v2/auth')) {
                return Http::response(['token' => 'tv-token', 'expiresAt' => now()->addHour()->toISOString()]);
            }

            if (str_contains($url, '/api/pub/v2/sms')) {
                return Http::response(['data' => [[
                    'id' => 'sms_1',
                    'to' => '+15551234567',
                    'from' => '+15550000000',
                    'createdAt' => '2026-01-01T00:01:00Z',
                    'encrypted' => false,
                    'smsContent' => 'Your verification code is 123456',
                    'parsedCode' => '123456',
                ]], 'hasNext' => false, 'hasPrevious' => false, 'count' => 1]);
            }

            if ($request->method() === 'GET' && str_contains($url, '/api/pub/v2/verifications/')) {
                return Http::response([
                    'id' => 'ver_123',
                    'state' => $completed ? 'verificationCompleted' : 'verificationPending',
                    'number' => '+15551234567',
                    'serviceName' => 'WhatsApp',
                    'endsAt' => '2026-01-01T00:00:00Z',
                ]);
            }

            if (str_contains($url, '/api/pub/v2/verifications')) {
                return Http::response(['id' => 'ver_123', 'state' => 'verificationPending'], 201);
            }

            return Http::response([], 404);
        });

        $result = app(TextVerifiedSupplierAdapter::class)->placeOrder($order);

        $this->assertSame('success', $result->status);
        $this->assertSame('ver_123', $result->reference);
        $this->assertSame('verificationPending', $result->data['supplier_status']);
        $this->assertSame('+15551234567', $result->data['delivery']['number'] ?? null);
        $this->assertTrue(SupplierLog::where('action', 'verification.create')->exists());
        $this->assertTrue(SupplierLog::where('action', 'verification.details')->exists());
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/pub/v2/auth')
                && $request->body() === '{}'
                && $request->hasHeader('X-API-KEY', 'tv-key')
                && $request->hasHeader('X-API-USERNAME', 'tv-user');
        });

        $completed = true;
        $order->forceFill(['supplier_reference' => 'ver_123', 'status' => Order::STATUS_SUBMITTED])->save();
        $status = app(TextVerifiedSupplierAdapter::class)->orderStatus($order->fresh('product'));

        $this->assertSame('completed', $status->data['status']);
        $this->assertSame('verificationCompleted', $status->data['supplier_status']);
        $this->assertSame('+15551234567', $status->data['delivery']['number'] ?? null);
        $this->assertSame('123456', $status->data['delivery']['sms_code'] ?? null);
        $this->assertTrue(SupplierLog::where('action', 'verification.sms')->exists());
    }

    public function test_textverified_does_not_support_non_verification_products(): void
    {
        config()->set('textverified.enabled', true);
        config()->set('textverified.api_key', 'tv-key');
        config()->set('textverified.api_username', 'tv-user');

        $product = Product::create([
            'name' => 'SMM Service',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'textverified',
            'supplier_adapter' => 'textverified',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->assertFalse(app(TextVerifiedSupplierAdapter::class)->supports($product));
    }

    public function test_herosms_places_activation_and_receives_sms(): void
    {
        config()->set('herosms.enabled', true);
        config()->set('herosms.base_url', 'https://herosms.test/api/v1');
        config()->set('herosms.api_key', 'hero-key');

        $product = $this->heroSmsProduct();
        $order = $this->orderFor($product);

        $this->assertInstanceOf(HeroSmsSupplierAdapter::class, app(SupplierManager::class)->adapterFor($product));
        $this->assertTrue(app(SupplierManager::class)->adapterFor($product)->supports($product));

        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/otp/last')) {
                return Http::response(['data' => [
                    'smsCode' => '123456',
                    'smsText' => 'Your verification code is 123456',
                    'from' => '+237699999999',
                ]]);
            }

            if (str_contains($url, '/finish')) {
                return Http::response(['status' => 'FINISHED']);
            }

            if (str_ends_with($url, '/activations')) {
                return Http::response(['data' => [[
                    'id' => 'act_1',
                    'phone' => '+237600000000',
                    'status' => 'WAIT',
                    'expiredAt' => '2026-01-01T00:00:00Z',
                    'activationCost' => 1.5,
                ]]]);
            }

            return Http::response([], 404);
        });

        $result = app(HeroSmsSupplierAdapter::class)->placeOrder($order);

        $this->assertSame('success', $result->status);
        $this->assertSame('act_1', $result->reference);
        $this->assertSame('+237600000000', $result->data['delivery']['phone_number'] ?? null);
        $this->assertTrue(SupplierLog::where('action', 'activation.create')->exists());

        $order->forceFill(['supplier_reference' => 'act_1', 'status' => Order::STATUS_SUBMITTED])->save();
        $status = app(HeroSmsSupplierAdapter::class)->orderStatus($order->fresh('product'));

        $this->assertSame('completed', $status->data['status']);
        $this->assertSame('SMS_RECEIVED', $status->data['supplier_status']);
        $this->assertSame('123456', $status->data['delivery']['sms_code'] ?? null);
        $this->assertTrue(SupplierLog::where('action', 'activation.otp_last')->exists());
        $this->assertTrue(SupplierLog::where('action', 'activation.finish')->exists());
    }

    public function test_herosms_requires_country_mapping_before_http(): void
    {
        config()->set('herosms.enabled', true);
        config()->set('herosms.api_key', 'hero-key');

        Http::fake();

        $product = Product::create([
            'name' => 'Hero Number',
            'type' => 'virtual_number',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'herosms',
            'supplier_adapter' => 'herosms',
            'meta' => ['service_name' => 'telegram'],
            'status' => 'active',
            'is_active' => true,
        ]);

        $result = app(HeroSmsSupplierAdapter::class)->placeOrder($this->orderFor($product));

        $this->assertSame('failed', $result->status);
        $this->assertTrue($result->safeToRefund);
        $this->assertSame(0, SupplierLog::count());
        Http::assertNothingSent();
    }

    public function test_justanotherpanel_places_and_checks_smm_order(): void
    {
        config()->set('justanotherpanel.base_url', 'https://panel.test');
        config()->set('justanotherpanel.endpoint', '/api/v2');
        config()->set('justanotherpanel.api_key', 'jap-key');

        $product = Product::create([
            'name' => 'JustAnotherPanel Instagram Followers',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'justanotherpanel',
            'supplier_adapter' => 'justanotherpanel',
            'supplier_product_id' => '123',
            'status' => 'active',
            'is_active' => true,
        ]);
        $order = $this->orderFor($product);
        $order->forceFill([
            'type' => 'smm_service',
            'quantity' => 100,
            'payload' => ['link' => 'https://instagram.com/example'],
        ])->save();

        $this->assertInstanceOf(JustAnotherPanelSupplierAdapter::class, app(SupplierManager::class)->adapterFor($product));
        $this->assertTrue(app(SupplierManager::class)->adapterFor($product)->supports($product));

        Http::fake(function ($request) {
            $data = $request->data();

            if (($data['action'] ?? null) === 'add') {
                return Http::response(['order' => 23501]);
            }

            if (($data['action'] ?? null) === 'status') {
                return Http::response([
                    'charge' => '0.001',
                    'start_count' => '0',
                    'status' => 'Completed',
                    'remains' => '0',
                    'currency' => 'USD',
                ]);
            }

            return Http::response([], 404);
        });

        $result = app(JustAnotherPanelSupplierAdapter::class)->placeOrder($order->fresh('product'));

        $this->assertSame('success', $result->status);
        $this->assertSame('23501', $result->reference);
        $this->assertSame('processing', $result->data['status']);
        $this->assertSame('Pending', $result->data['supplier_status']);
        $this->assertTrue(SupplierLog::where('action', 'order.create')->exists());
        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_contains($request->url(), '/api/v2')
                && ($data['action'] ?? null) === 'add'
                && ($data['key'] ?? null) === 'jap-key'
                && ($data['service'] ?? null) === 123
                && ($data['quantity'] ?? null) === 100
                && ! array_key_exists('idempotency_key', $data);
        });

        $order->forceFill(['supplier_reference' => '23501', 'status' => Order::STATUS_SUBMITTED])->save();
        $status = app(JustAnotherPanelSupplierAdapter::class)->orderStatus($order->fresh('product'));

        $this->assertSame('completed', $status->data['status']);
        $this->assertSame('Completed', $status->data['supplier_status']);
        $this->assertSame('0', $status->data['delivery']['remains'] ?? null);
        $this->assertTrue(SupplierLog::where('action', 'order.status')->exists());
    }

    public function test_justanotherpanel_requires_link_before_http(): void
    {
        config()->set('justanotherpanel.base_url', 'https://panel.test');
        config()->set('justanotherpanel.api_key', 'jap-key');

        Http::fake();

        $product = Product::create([
            'name' => 'JustAnotherPanel TikTok Likes',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'justanotherpanel',
            'supplier_adapter' => 'justanotherpanel',
            'supplier_product_id' => '456',
            'status' => 'active',
            'is_active' => true,
        ]);
        $order = $this->orderFor($product);
        $order->forceFill(['type' => 'smm_service', 'quantity' => 100, 'payload' => []])->save();

        $result = app(JustAnotherPanelSupplierAdapter::class)->placeOrder($order->fresh('product'));

        $this->assertSame('failed', $result->status);
        $this->assertTrue($result->safeToRefund);
        $this->assertSame(0, SupplierLog::count());
        Http::assertNothingSent();
    }

    public function test_justanotherpanel_sync_archives_products_removed_from_catalog(): void
    {
        config()->set('justanotherpanel.base_url', 'https://panel.test');
        config()->set('justanotherpanel.endpoint', '/api/v2');
        config()->set('justanotherpanel.api_key', 'jap-key');

        $keep = Product::create([
            'name' => 'JAP Kept Service',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'justanotherpanel',
            'supplier_adapter' => 'justanotherpanel',
            'supplier_product_id' => '123',
            'status' => 'active',
            'is_active' => true,
        ]);
        $removed = Product::create([
            'name' => 'JAP Removed Service',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'justanotherpanel',
            'supplier_adapter' => 'justanotherpanel',
            'supplier_product_id' => '456',
            'status' => 'active',
            'is_active' => true,
        ]);
        $alreadyArchived = Product::create([
            'name' => 'JAP Already Archived Service',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'justanotherpanel',
            'supplier_adapter' => 'justanotherpanel',
            'supplier_product_id' => '789',
            'status' => 'archived',
            'is_active' => false,
        ]);

        Http::fake(function ($request) {
            $data = $request->data();

            if (($data['action'] ?? null) === 'services') {
                return Http::response([
                    ['service' => 123, 'name' => 'Instagram Followers', 'rate' => 1, 'type' => 'Default', 'category' => 'Instagram', 'min' => 1, 'max' => 1000],
                ]);
            }

            return Http::response([], 404);
        });

        $result = app(JustAnotherPanelCatalogImporter::class)->sync(['dry_run' => false]);

        $this->assertSame(1, $result['catalog_count']);
        $this->assertSame(3, $result['products']);
        $this->assertSame(2, $result['missing']);
        $this->assertSame(1, $result['candidates']);
        $this->assertSame(1, $result['archived']);

        $keep->refresh();
        $removed->refresh();
        $alreadyArchived->refresh();

        $this->assertTrue($keep->is_active);
        $this->assertSame('active', $keep->status);
        $this->assertFalse($removed->is_active);
        $this->assertSame('archived', $removed->status);
        $this->assertFalse($alreadyArchived->is_active);
        $this->assertSame('archived', $alreadyArchived->status);
    }

    public function test_justanotherpanel_sync_does_not_archive_when_catalog_is_unavailable(): void
    {
        config()->set('justanotherpanel.base_url', 'https://panel.test');
        config()->set('justanotherpanel.endpoint', '/api/v2');
        config()->set('justanotherpanel.api_key', 'jap-key');

        Http::fake(['*' => Http::response([])]);

        $product = Product::create([
            'name' => 'JAP Active Service',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'justanotherpanel',
            'supplier_adapter' => 'justanotherpanel',
            'supplier_product_id' => '123',
            'status' => 'active',
            'is_active' => true,
        ]);

        $result = app(JustAnotherPanelCatalogImporter::class)->sync(['dry_run' => false]);

        $this->assertSame('catalog_unavailable', $result['error']);
        $this->assertSame(0, $result['archived']);
        $product->refresh();
        $this->assertTrue($product->is_active);
        $this->assertSame('active', $product->status);
    }

    private function textVerifiedProduct(): Product
    {
        return Product::create([
            'name' => 'TextVerified WhatsApp',
            'type' => 'virtual_number',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'textverified',
            'supplier_adapter' => 'textverified',
            'meta' => ['service_name' => 'WhatsApp', 'capability' => 'sms'],
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function heroSmsProduct(): Product
    {
        return Product::create([
            'name' => 'HeroSMS Telegram',
            'type' => 'virtual_number',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'herosms',
            'supplier_adapter' => 'herosms',
            'meta' => ['service_name' => 'telegram', 'country_id' => '1'],
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function orderFor(Product $product): Order
    {
        $user = User::factory()->create();

        return Order::create([
            'order_number' => 'TVS-TEST-'.Str::random(8),
            'user_id' => $user->id,
            'product_id' => $product->id,
            'type' => 'virtual_number',
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
            'currency' => 'XAF',
            'status' => Order::STATUS_SUBMITTED,
            'idempotency_key' => 'order-'.Str::random(12),
            'payload' => [],
            'paid_at' => now(),
            'submitted_at' => now(),
        ]);
    }
}
