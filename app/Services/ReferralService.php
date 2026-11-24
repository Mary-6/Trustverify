<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    public function __construct(
        private WalletService $wallets,
        private SettingsService $settings,
    ) {}

    public function ensureCode(User $user): string
    {
        return $user->ensureReferralCode();
    }

    public function referralUrl(User $user): string
    {
        return route('register', ['ref' => $this->ensureCode($user)]);
    }

    public function attach(User $user, ?string $code): void
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '' || $user->referred_by_user_id) {
            return;
        }

        $referrer = User::where('referral_code', $code)->first();
        if (! $referrer || $referrer->id === $user->id) {
            return;
        }

        $user->forceFill(['referred_by_user_id' => $referrer->id])->save();
    }

    public function rewardFirstDeposit(User $user): void
    {
        if (! $user->referred_by_user_id || $user->referral_rewarded_at) {
            return;
        }

        $amount = (float) $this->settings->get('referrals.reward_amount', 0);
        if (! Money::isPositive($amount)) {
            return;
        }

        DB::transaction(function () use ($user, $amount) {
            $fresh = User::whereKey($user->id)->lockForUpdate()->first();
            if (! $fresh || $fresh->referral_rewarded_at || ! $fresh->referred_by_user_id) {
                return;
            }

            $referrer = User::find($fresh->referred_by_user_id);
            if (! $referrer) {
                return;
            }

            $this->wallets->credit($referrer, $amount, WalletTransaction::TYPE_REFERRAL, [
                'idempotency_key' => 'referral-reward-'.$fresh->id,
                'reference' => 'referral-'.$fresh->id,
                'metadata' => [
                    'referred_user_id' => $fresh->id,
                    'referred_email' => $fresh->email,
                ],
            ]);

            $fresh->forceFill(['referral_rewarded_at' => now()])->save();
        });
    }
}
