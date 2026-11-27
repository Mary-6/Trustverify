<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_refill_for_eligible_completed_smm_order(): void
    {
        $this->configureJap();
        $user = User::factory()->create();
        $order = $this->order($this->product(), $user, Order::STATUS_COMPLETED);

        Http::fake(function ($request) {
            $data = $request->data();

            if (($data['action'] ?? null) === 'refill') {
                return Http::response(['order' => 23501, 'refill' => 777]);
            }

            return Http::response([], 404);
        });

        $this->actingAs($user)
            ->post(route('dashboard.orders.refill', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('777', (string) ($order->delivery_data['refill_id'] ?? ''));
        $this->assertNotEmpty($order->delivery_data['refill_requested_at'] ?? null);
        $this->assertTrue(SupplierLog::where('action', 'order.refill')->exists());
        $this->assertGreaterThan(0, $user->notifications()->count());
    }

    public function test_customer_can_request_cancel_for_in_progress_smm_order(): void
    {
        $this->configureJap();
        $user = User::factory()->create();
        $order = $this->order($this->product(), $user, Order::STATUS_PROCESSING);

        Http::fake(function ($request) {
            $data = $request->data();

            if (($data['action'] ?? null) === 'status') {
                return Http::response([
                    'charge' => '0.5',
                    'start_count' => '5000',
                    'status' => 'In progress',
                    'remains' => '60',
                    'currency' => 'USD',
                ]);
            }

            if (($data['action'] ?? null) === 'cancel') {
                return Http::response([['order' => 23501, 'cancel' => 1]]);
            }

            return Http::response([], 404);
        });

        $this->actingAs($user)
            ->post(route('dashboard.orders.cancel', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertNotEmpty($order->delivery_data['cancel_requested_at'] ?? null);
        $this->assertSame(60.0, (float) ($order->delivery_data['remains'] ?? 0));
        $this->assertTrue(SupplierLog::where('action', 'order.cancel')->exists());
        $this->assertGreaterThan(0, $user->notifications()->count());
    }

    public function test_order_detail_shows_progress_back_button_and_eligible_actions(): void
    {
        $this->configureJap();
        $user = User::factory()->create();
        $order = $this->order($this->product(), $user, Order::STATUS_COMPLETED);
        $order->forceFill([
            'quantity' => 100,
            'delivery_data' => [
                'start_count' => '5000',
                'remains' => '40',
                'charge' => '0.5',
                'currency' => 'USD',
            ],
        ])->save();

        $this->actingAs($user)
            ->get(route('dashboard.orders.show', $order))
            ->assertOk()
            ->assertSee('←', false)
            ->assertSee('My Orders')
            ->assertSee('Delivery progress')
            ->assertSee('Start count')
            ->assertSee('Remaining')
            ->assertSee('60%', false)
            ->assertSee('Request refill')
            ->assertDontSee('Request cancellation');
    }

    public function test_order_actions_are_owner_only(): void
    {
        $this->configureJap();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->order($this->product(), $owner, Order::STATUS_COMPLETED);

        Http::fake();

        $this->actingAs($other)
            ->post(route('dashboard.orders.refill', $order))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    private function configureJap(): void
    {
        config()->set('justanotherpanel.base_url', 'https://panel.test');
        config()->set('justanotherpanel.endpoint', '/api/v2');
        config()->set('justanotherpanel.api_key', 'jap-key');
    }

    private function product(): Product
    {
        return Product::create([
            'name' => 'Instagram Followers Operations Test',
            'type' => 'smm_service',
            'summary' => 'General Instagram followers.',
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => 'justanotherpanel',
            'supplier_adapter' => 'justanotherpanel',
            'supplier_product_id' => '123',
            'meta' => [
                'supplier_category' => 'Instagram Services [WORLD]',
                'refill' => true,
                'cancel' => true,
            ],
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function order(Product $product, User $user, string $status): Order
    {
        return Order::create([
            'order_number' => 'TVS-OPS-'.Str::random(8),
            'user_id' => $user->id,
            'product_id' => $product->id,
            'type' => 'smm_service',
            'product_name' => $product->name,
            'quantity' => 100,
            'unit_price' => 10,
            'total_price' => 1000,
            'currency' => 'XAF',
            'status' => $status,
            'supplier_reference' => '23501',
            'supplier_status' => 'In progress',
            'idempotency_key' => 'order-'.Str::random(12),
            'payload' => ['link' => 'https://instagram.com/example'],
            'delivery_data' => [],
            'paid_at' => now(),
            'submitted_at' => now(),
        ]);
    }
}
