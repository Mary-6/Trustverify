<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_pages_render_real_apps_and_countries(): void
    {
        Country::create([
            'name' => 'United States',
            'code' => 'US',
            'dial_code' => '+1',
            'currency' => 'USD',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->verificationProduct('WhatsApp US SMS Verification', 'whatsapp', 986.45);
        Product::create([
            'name' => 'Instagram Followers SMM',
            'type' => 'smm_service',
            'price' => 100,
            'currency' => 'XAF',
            'status' => 'active',
            'is_active' => true,
        ]);

        foreach (['/verification', '/services?service=virtual_number'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('STEP 1 – Choose an app')
                ->assertSee('WhatsApp')
                ->assertSee('United States')
                ->assertSee('flagcdn.com', false)
                ->assertDontSee('Instagram Followers SMM');
        }
    }

    public function test_guest_cannot_purchase_verification_number(): void
    {
        $product = $this->verificationProduct('WhatsApp US SMS Verification', 'whatsapp', 986.45);

        $this->post(route('dashboard.verification.orders.store'), [
            'product_id' => $product->id,
            'app_slug' => 'whatsapp',
            'country_code' => 'US',
            'idempotency_key' => 'guest-verification',
        ])->assertRedirect();

        $this->assertGuest();
        $this->assertSame(0, Order::count());
    }

    public function test_provider_offline_blocks_purchase_before_wallet_debit(): void
    {
        config(['textverified.enabled' => false]);

        $user = User::factory()->create();
        app(WalletService::class)->credit($user, 5000, WalletTransaction::TYPE_DEPOSIT, ['idempotency_key' => 'seed-provider-offline']);
        $product = $this->verificationProduct('WhatsApp US SMS Verification', 'whatsapp', 986.45);

        $this->actingAs($user)->post(route('dashboard.verification.orders.store'), [
            'product_id' => $product->id,
            'app_slug' => 'whatsapp',
            'country_code' => 'US',
            'idempotency_key' => 'provider-offline-verification',
        ])->assertSessionHasErrors('product');

        $this->assertSame(0, Order::count());
        $this->assertSame(0, WalletTransaction::where('type', WalletTransaction::TYPE_PURCHASE)->count());
        $this->assertSame(5000, (int) app(WalletService::class)->walletFor($user)->fresh()->balance);
    }

    public function test_insufficient_wallet_balance_blocks_verification_purchase(): void
    {
        config([
            'textverified.enabled' => true,
            'textverified.api_key' => 'test-key',
            'textverified.api_username' => 'test@example.com',
            'textverified.base_url' => 'https://example.com',
        ]);

        $user = User::factory()->create();
        app(WalletService::class)->credit($user, 100, WalletTransaction::TYPE_DEPOSIT, ['idempotency_key' => 'seed-insufficient']);
        $product = $this->verificationProduct('WhatsApp US SMS Verification', 'whatsapp', 986.45);

        $this->actingAs($user)->post(route('dashboard.verification.orders.store'), [
            'product_id' => $product->id,
            'app_slug' => 'whatsapp',
            'country_code' => 'US',
            'idempotency_key' => 'insufficient-verification',
        ])->assertRedirect(route('dashboard.wallet.index', ['return_to' => route('verification.index', [], false)]))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(100, (int) app(WalletService::class)->walletFor($user)->fresh()->balance);
    }

    public function test_pending_verification_order_can_be_cancelled_once_with_wallet_refund(): void
    {
        $user = User::factory()->create();
        app(WalletService::class)->credit($user, 1000, WalletTransaction::TYPE_DEPOSIT, ['idempotency_key' => 'seed-cancel']);

        $product = Product::create([
            'name' => 'Manual US SMS Verification',
            'type' => 'virtual_number',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'manual',
            'supplier_adapter' => 'manual',
            'meta' => ['country' => 'US', 'pricing_unit' => 'per_unit'],
            'status' => 'active',
            'is_active' => true,
            'min_order' => 1,
            'max_order' => 1,
        ]);

        $orders = app(OrderService::class);
        $order = $orders->place($user, $product, [], 1, 'manual-verification-cancel');
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(900, (int) app(WalletService::class)->walletFor($user)->fresh()->balance);

        $orders->cancel($order, $user, 'Test cancellation');
        $orders->cancel($order->fresh(), $user, 'Test cancellation repeat');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(1000, (int) app(WalletService::class)->walletFor($user)->fresh()->balance);
        $this->assertSame(1, WalletTransaction::where('type', WalletTransaction::TYPE_PURCHASE)->count());
        $this->assertSame(1, WalletTransaction::where('type', WalletTransaction::TYPE_REFUND)->count());
    }

    public function test_account_pages_hide_reseller_api_tickets_and_coming_soon_methods(): void
    {
        $user = User::factory()->create();

        config([
            'fapshi.enabled' => true,
            'fapshi.api_user' => 'api-user',
            'fapshi.api_key' => 'api-key',
        ]);

        $this->actingAs($user)->get(route('dashboard.overview'))
            ->assertOk()
            ->assertSee('Account management')
            ->assertSee('Deposit')
            ->assertDontSee('Add Money')
            ->assertDontSee('API for resellers')
            ->assertDontSee('Support tickets')
            ->assertDontSee('Open tickets');

        $this->actingAs($user)->get(route('dashboard.wallet.index'))
            ->assertOk()
            ->assertSee('MTN Mobile Money')
            ->assertDontSee('Coming soon')
            ->assertDontSee('Orange Money')
            ->assertDontSee('Visa/Mastercard')
            ->assertDontSee('PayPal');
    }

    private function verificationProduct(string $name, string $serviceName, float $price): Product
    {
        return Product::create([
            'name' => $name,
            'type' => 'virtual_number',
            'price' => $price,
            'currency' => 'XAF',
            'supplier' => 'textverified',
            'supplier_product_id' => $serviceName,
            'supplier_adapter' => 'textverified',
            'meta' => [
                'country' => 'US',
                'pricing_unit' => 'per_unit',
                'textverified' => [
                    'service_name' => $serviceName,
                    'capability' => 'sms',
                    'number_type' => 'mobile',
                ],
            ],
            'status' => 'active',
            'is_active' => true,
            'min_order' => 1,
            'max_order' => 1,
        ]);
    }
}
