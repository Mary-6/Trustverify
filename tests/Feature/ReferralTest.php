<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\ReferralService;
use App\Services\SettingsService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_reward_is_credited_once_after_first_deposit(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referred_by_user_id' => $referrer->id]);

        app(SettingsService::class)->set('referrals.reward_amount', 500, 'integer', 'referrals');

        $wallets = app(WalletService::class);
        $referrals = app(ReferralService::class);

        $wallets->credit($referred, 1000, WalletTransaction::TYPE_DEPOSIT);
        $referrals->rewardFirstDeposit($referred);
        $referrals->rewardFirstDeposit($referred);

        $referrerWallet = $wallets->walletFor($referrer);

        $this->assertSame('500.00', (string) $referrerWallet->fresh()->balance);
        $this->assertNotNull($referred->fresh()->referral_rewarded_at);
        $this->assertSame(1, WalletTransaction::where('user_id', $referrer->id)->where('type', WalletTransaction::TYPE_REFERRAL)->count());
    }

    public function test_referral_reward_is_skipped_when_amount_is_zero(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referred_by_user_id' => $referrer->id]);

        app(SettingsService::class)->set('referrals.reward_amount', 0, 'integer', 'referrals');

        $wallets = app(WalletService::class);
        $wallets->credit($referred, 1000, WalletTransaction::TYPE_DEPOSIT);
        app(ReferralService::class)->rewardFirstDeposit($referred);

        $this->assertSame('0.00', (string) $wallets->walletFor($referrer)->fresh()->balance);
        $this->assertNull($referred->fresh()->referral_rewarded_at);
    }
}
