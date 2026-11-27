<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FapshiPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function configureFapshi(): void
    {
        config([
            'fapshi.enabled' => true,
            'fapshi.base_url' => 'https://fapshi.test',
            'fapshi.api_user' => 'api-user',
            'fapshi.api_key' => 'api-key',
            'fapshi.webhook_secret' => 'webhook-secret',
        ]);
    }

    public function test_fapshi_deposit_creates_payment_link(): void
    {
        $this->configureFapshi();

        Http::fake([
            'https://fapshi.test/initiate-pay' => Http::response([
                'message' => 'Payment link created',
                'link' => 'https://fapshi.test/pay/abc123',
                'transId' => 'TRX123',
                'dateInitiated' => '2026-11-17',
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('dashboard.wallet.deposit'), [
            'amount' => 1000,
            'method' => 'mtn_momo',
            'phone' => '670000000',
            'idempotency_key' => 'deposit-test-1',
        ]);

        $response->assertRedirect('https://fapshi.test/pay/abc123');

        $payment = Payment::where('user_id', $user->id)->first();

        $this->assertNotNull($payment);
        $this->assertSame('fapshi', $payment->gateway);
        $this->assertSame('TRX123', $payment->reference);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertNotEmpty($payment->payload['external_id']);
    }

    public function test_deposit_requires_fapshi_and_does_not_create_manual_payment(): void
    {
        config([
            'fapshi.enabled' => false,
            'fapshi.api_user' => null,
            'fapshi.api_key' => null,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('dashboard.wallet.deposit'), [
            'amount' => 1000,
            'method' => 'mtn_momo',
            'phone' => '670000000',
            'idempotency_key' => 'deposit-manual-blocked',
        ])->assertSessionHas('error');

        $this->assertSame(0, Payment::count());
    }

    public function test_fapshi_webhook_credits_wallet_once_after_verification(): void
    {
        $this->configureFapshi();

        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'currency' => 'XAF',
            'gateway' => 'fapshi',
            'reference' => 'TRX123',
            'status' => Payment::STATUS_PENDING,
            'idempotency_key' => 'deposit-webhook-1',
            'payload' => ['external_id' => 'FAP-TEST123', 'fapshi_user_id' => 'u'.$user->id],
        ]);

        Http::fake([
            'https://fapshi.test/payment-status/TRX123' => Http::response([
                'transId' => 'TRX123',
                'status' => 'SUCCESSFUL',
                'amount' => 1000,
                'externalId' => 'FAP-TEST123',
                'userId' => 'u'.$user->id,
            ], 200),
        ]);

        $payload = [
            'transId' => 'TRX123',
            'status' => 'SUCCESSFUL',
            'amount' => 1000,
            'externalId' => 'FAP-TEST123',
            'userId' => 'u'.$user->id,
        ];

        $this->postJson('/api/webhooks/fapshi', $payload, ['x-wh-secret' => 'webhook-secret'])->assertOk();

        $payment->refresh();
        $wallet = Wallet::where('user_id', $user->id)->first();

        $this->assertSame(Payment::STATUS_SUCCESS, $payment->status);
        $this->assertNotNull($payment->verified_at);
        $this->assertSame('1000.00', (string) $wallet->balance);
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->where('type', 'deposit')->count());

        $this->postJson('/api/webhooks/fapshi', $payload, ['x-wh-secret' => 'webhook-secret'])->assertOk();

        $this->assertSame('1000.00', (string) $wallet->fresh()->balance);
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->where('type', 'deposit')->count());
    }

    public function test_admin_cannot_manually_verify_non_fapshi_payment(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => 'active']);
        $user = User::factory()->create();

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'currency' => 'XAF',
            'gateway' => 'manual',
            'reference' => 'MANUAL-1',
            'status' => Payment::STATUS_PENDING,
            'idempotency_key' => 'manual-payment-1',
        ]);

        $this->actingAs($admin)->post(route('admin.payments.verify', $payment))->assertStatus(422);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(0, WalletTransaction::count());
    }

    public function test_admin_can_sync_successful_fapshi_payment(): void
    {
        $this->configureFapshi();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => 'active']);
        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'currency' => 'XAF',
            'gateway' => 'fapshi',
            'reference' => 'TRX999',
            'status' => Payment::STATUS_PENDING,
            'idempotency_key' => 'fapshi-admin-sync-1',
            'payload' => ['external_id' => 'FAP-ADMIN123'],
        ]);

        Http::fake([
            'https://fapshi.test/payment-status/TRX999' => Http::response([
                'transId' => 'TRX999',
                'status' => 'SUCCESSFUL',
                'amount' => 1000,
                'externalId' => 'FAP-ADMIN123',
                'userId' => 'u'.$user->id,
            ], 200),
        ]);

        $this->actingAs($admin)->post(route('admin.payments.verify', $payment))
            ->assertSessionHas('status', 'Fapshi payment verified and wallet credited.');

        $payment->refresh();
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        $this->assertSame(Payment::STATUS_SUCCESS, $payment->status);
        $this->assertSame('1000.00', (string) $wallet->balance);
        $this->assertSame('SUCCESSFUL', $payment->payload['verified_transaction']['status']);
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->where('type', 'deposit')->count());
    }

    public function test_fapshi_webhook_rejects_invalid_secret(): void
    {
        $this->configureFapshi();

        $this->postJson('/api/webhooks/fapshi', ['transId' => 'TRX123'], ['x-wh-secret' => 'wrong'])
            ->assertForbidden();

        $this->assertSame(0, WalletTransaction::count());
    }
}
