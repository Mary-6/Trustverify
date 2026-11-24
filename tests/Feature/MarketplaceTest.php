<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk();
        $this->get('/services')->assertOk();
        $this->get('/pricing')->assertOk();
        $this->get('/api')->assertOk();
        $this->get('/support')->assertOk();
    }

    public function test_services_page_hides_unavailable_services(): void
    {
        $instagram = Category::create([
            'name' => 'Instagram',
            'slug' => 'instagram',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'name' => 'Instagram Followers Test',
            'type' => 'smm_service',
            'category_id' => $instagram->id,
            'price' => 10,
            'currency' => 'XAF',
            'supplier_adapter' => 'manual',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->get('/services?service=smm_service')
            ->assertOk()
            ->assertSee('Choose a platform')
            ->assertDontSee('Instagram Followers Test');

        $this->get('/services?service=smm_service&brand=instagram')
            ->assertOk()
            ->assertSee('What do you need on Instagram')
            ->assertSee('Followers')
            ->assertDontSee('Instagram Followers Test');

        $this->get('/services?service=smm_service&brand=instagram&group=followers')
            ->assertOk()
            ->assertSee('Instagram Followers Test');

        $this->get('/services?service=smm_service&brand=snapchat')
            ->assertOk()
            ->assertSee('Choose a platform');

        $this->get('/services?service=seo_service')
            ->assertOk()
            ->assertSee('SMM Services')
            ->assertDontSee('SEO Services');

        $this->get('/services?service=virtual_number')
            ->assertOk()
            ->assertSee('SMM Services')
            ->assertSee('Choose a platform')
            ->assertDontSee('This service is not available yet')
            ->assertDontSee('Coming soon');

        $this->get('/services?type=esim')
            ->assertOk()
            ->assertSee('Choose a platform')
            ->assertDontSee('eSIM Marketplace');

        $this->get('/services')
            ->assertOk()
            ->assertSee('SMM Services')
            ->assertDontSee('value="virtual_number"', false)
            ->assertDontSee('SEO Services')
            ->assertDontSee('Social Media Accounts')
            ->assertDontSee('Advertising Accounts')
            ->assertDontSee('Long-term Numbers')
            ->assertDontSee('eSIM Marketplace')
            ->assertDontSee('Other Digital Products');
    }

    public function test_customer_can_purchase_with_wallet_and_idempotency_key(): void
    {
        $user = User::factory()->create();
        $wallet = app(WalletService::class)->walletFor($user);
        app(WalletService::class)->credit($user, 1000, WalletTransaction::TYPE_DEPOSIT, ['idempotency_key' => 'seed-deposit']);

        $product = Product::create([
            'name' => 'Test SMM Service',
            'type' => 'smm_service',
            'summary' => 'A safe test product.',
            'price' => 100,
            'currency' => 'XAF',
            'supplier_adapter' => 'manual',
            'status' => 'active',
            'is_active' => true,
        ]);

        $payload = [
            'quantity' => 2,
            'idempotency_key' => 'test-order-key',
        ];

        $this->actingAs($user)->post(route('dashboard.orders.store', $product), $payload)
            ->assertRedirect();

        $this->actingAs($user)->post(route('dashboard.orders.store', $product), $payload)
            ->assertRedirect();

        $this->assertSame(1, Order::count());
        $this->assertSame(800, (int) $wallet->fresh()->balance);
        $this->assertSame(1, WalletTransaction::where('type', WalletTransaction::TYPE_PURCHASE)->count());
    }

    public function test_api_requires_token_and_returns_wallet(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        app(WalletService::class)->credit($user, 500, WalletTransaction::TYPE_DEPOSIT, ['idempotency_key' => 'api-seed']);

        $plain = 'tvsk_'.\Illuminate\Support\Str::random(48);
        $user->apiKeys()->create([
            'name' => 'Test key',
            'token_hash' => hash('sha256', $plain),
            'prefix' => substr($plain, 0, 12),
            'abilities' => ['wallet:read', 'products:read', 'orders:read'],
        ]);

        $this->getJson('/api/v1/wallet')->assertUnauthorized();
        $this->withHeader('Authorization', 'Bearer '.$plain)->getJson('/api/v1/wallet')
            ->assertOk()
            ->assertJsonPath('data.balance', 500);
    }

    public function test_api_key_abilities_are_enforced(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $plain = 'tvsk_'.\Illuminate\Support\Str::random(48);
        $user->apiKeys()->create([
            'name' => 'Products only',
            'token_hash' => hash('sha256', $plain),
            'prefix' => substr($plain, 0, 12),
            'abilities' => ['products:read'],
        ]);

        $this->withHeader('Authorization', 'Bearer '.$plain)->getJson('/api/v1/wallet')
            ->assertForbidden();
        $this->withHeader('Authorization', 'Bearer '.$plain)->getJson('/api/v1/products')
            ->assertOk();
    }

    public function test_admin_refund_is_idempotent(): void
    {
        $customer = User::factory()->create();
        app(WalletService::class)->credit($customer, 1000, WalletTransaction::TYPE_DEPOSIT, ['idempotency_key' => 'refund-test-deposit']);

        $product = Product::create([
            'name' => 'Refundable Product',
            'type' => 'smm_service',
            'price' => 200,
            'currency' => 'XAF',
            'supplier_adapter' => 'manual',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($customer)->post(route('dashboard.orders.store', $product), [
            'quantity' => 1,
            'idempotency_key' => 'refund-order-key',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => 'active']);

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['action' => 'refund'])
            ->assertRedirect();

        $this->assertSame(Order::STATUS_REFUNDED, $order->fresh()->status);
        $this->assertSame(1000, (int) app(WalletService::class)->walletFor($customer)->fresh()->balance);
        $this->assertSame(1, WalletTransaction::where('type', WalletTransaction::TYPE_REFUND)->count());

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['action' => 'refund'])
            ->assertRedirect();

        $this->assertSame(1, WalletTransaction::where('type', WalletTransaction::TYPE_REFUND)->count());
        $this->assertSame(1000, (int) app(WalletService::class)->walletFor($customer)->fresh()->balance);
    }

    public function test_per_1000_product_quotes_by_quantity(): void
    {
        $user = User::factory()->create();
        app(WalletService::class)->credit($user, 1000, WalletTransaction::TYPE_DEPOSIT, ['idempotency_key' => 'per1000-deposit']);

        $product = Product::create([
            'name' => 'Per 1000 SMM Service',
            'type' => 'smm_service',
            'price' => 6,
            'currency' => 'XAF',
            'supplier_adapter' => 'manual',
            'meta' => ['pricing_unit' => 'per_1000'],
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('dashboard.orders.store', $product), [
            'quantity' => 500,
            'idempotency_key' => 'per1000-order',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(3.0, (float) $order->total_price);
        $this->assertSame(6.0, (float) $order->unit_price);
        $this->assertSame(997, (int) app(WalletService::class)->walletFor($user)->fresh()->balance);
    }

    public function test_product_currency_must_match_wallet_currency(): void
    {
        $user = User::factory()->create();
        app(WalletService::class)->credit($user, 1000, WalletTransaction::TYPE_DEPOSIT, ['idempotency_key' => 'currency-deposit']);

        $product = Product::create([
            'name' => 'USD Product',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'USD',
            'supplier_adapter' => 'manual',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('dashboard.orders.store', $product), [
            'quantity' => 1,
            'idempotency_key' => 'currency-order',
        ])->assertSessionHasErrors('order');

        $this->assertSame(0, Order::count());
        $this->assertSame(1000, (int) app(WalletService::class)->walletFor($user)->fresh()->balance);
    }

    public function test_admin_dashboard_and_product_filters_render(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => 'active']);

        Product::create([
            'name' => 'Active Admin Product',
            'type' => 'smm_service',
            'price' => 10,
            'currency' => 'XAF',
            'supplier_adapter' => 'manual',
            'status' => 'active',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Archived Admin Product',
            'type' => 'smm_service',
            'price' => 20,
            'currency' => 'XAF',
            'supplier_adapter' => 'manual',
            'status' => 'archived',
            'is_active' => false,
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.products.index', ['status' => 'active']))->assertOk();
        $this->actingAs($admin)->get(route('admin.products.index', ['status' => 'archived']))->assertOk();
    }
}
