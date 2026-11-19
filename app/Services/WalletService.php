<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function walletFor(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['currency' => 'XAF', 'balance' => 0]
        );
    }

    public function credit(User $user, float|int|string $amount, string $type = WalletTransaction::TYPE_DEPOSIT, array $options = []): WalletTransaction
    {
        if (! Money::isPositive($amount)) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $options) {
            $wallet = $this->lockWallet($user);

            if (! empty($options['idempotency_key'])) {
                $existing = WalletTransaction::where('idempotency_key', $options['idempotency_key'])->first();
                if ($existing) {
                    return $existing;
                }
            }

            $balance = Money::add($wallet->balance, $amount);
            $wallet->forceFill(['balance' => $balance])->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => $type,
                'amount' => Money::normalize($amount),
                'balance_after' => $balance,
                'currency' => $wallet->currency,
                'status' => $options['status'] ?? 'completed',
                'reference' => $options['reference'] ?? null,
                'gateway' => $options['gateway'] ?? null,
                'idempotency_key' => $options['idempotency_key'] ?? null,
                'metadata' => $options['metadata'] ?? null,
                'created_by' => $options['created_by'] ?? null,
            ]);
        });
    }

    public function debit(User $user, float|int|string $amount, string $type = WalletTransaction::TYPE_PURCHASE, array $options = []): WalletTransaction
    {
        if (! Money::isPositive($amount)) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $options) {
            $wallet = $this->lockWallet($user);

            if (! empty($options['idempotency_key'])) {
                $existing = WalletTransaction::where('idempotency_key', $options['idempotency_key'])->first();
                if ($existing) {
                    return $existing;
                }
            }

            if (Money::compare($wallet->balance, $amount) < 0) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $balance = Money::sub($wallet->balance, $amount);
            $wallet->forceFill(['balance' => $balance])->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => $type,
                'amount' => Money::normalize($amount),
                'balance_after' => $balance,
                'currency' => $wallet->currency,
                'status' => $options['status'] ?? 'completed',
                'reference' => $options['reference'] ?? null,
                'gateway' => $options['gateway'] ?? null,
                'idempotency_key' => $options['idempotency_key'] ?? null,
                'metadata' => $options['metadata'] ?? null,
                'created_by' => $options['created_by'] ?? null,
            ]);
        });
    }

    private function lockWallet(User $user): Wallet
    {
        $wallet = $this->walletFor($user);
        return Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
    }
}
