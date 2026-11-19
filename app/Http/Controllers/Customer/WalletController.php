<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\FapshiGateway;
use App\Services\SettingsService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class WalletController extends Controller
{
    public function index(Request $request, WalletService $wallets)
    {
        $user = $request->user();
        $wallet = $wallets->walletFor($user);

        return view('customer.wallet.index', [
            'wallet' => $wallet,
            'transactions' => $user->walletTransactions()->latest()->paginate(20),
            'payments' => $user->payments()->latest()->paginate(10, ['*'], 'payments_page'),
            'paymentMethods' => $this->paymentMethodOptions(),
            'depositKey' => (string) Str::uuid(),
        ]);
    }

    public function deposit(Request $request, FapshiGateway $fapshi)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:100', 'max:100000000'],
            'method' => ['required', 'string', Rule::in($this->enabledPaymentMethods())],
            'phone' => ['nullable', 'string', 'max:30'],
            'reference_note' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['nullable', 'string', 'max:80'],
        ]);

        $user = $request->user();
        $wallet = app(WalletService::class)->walletFor($user);
        $amount = (int) $data['amount'];
        $method = $data['method'];
        $useFapshi = in_array($method, ['mtn_momo', 'orange_money'], true) && $fapshi->isConfigured();
        $idempotencyKey = 'deposit-'.$user->id.'-'.($data['idempotency_key'] ?: (string) Str::uuid());

        $existing = Payment::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            $link = $existing->payload['link'] ?? null;
            if ($useFapshi && $existing->status === Payment::STATUS_PENDING && is_string($link) && $link !== '') {
                return redirect()->away($link);
            }

            return back()->with('status', 'Deposit request already submitted.');
        }

        if ($useFapshi) {
            $externalId = 'FAP-'.strtoupper(Str::random(10));
            $fapshiUserId = 'u'.$user->id;

            try {
                $result = $fapshi->initiatePay(
                    $amount,
                    $fapshiUserId,
                    $externalId,
                    $user->email,
                    route('dashboard.wallet.index'),
                    'Wallet deposit'
                );
            } catch (Throwable $exception) {
                return back()->with('error', 'Fapshi payment could not be started: '.$exception->getMessage())->withInput();
            }

            Payment::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'currency' => $wallet->currency ?: 'XAF',
                'gateway' => 'fapshi',
                'reference' => $result['transId'],
                'status' => Payment::STATUS_PENDING,
                'idempotency_key' => $idempotencyKey,
                'payload' => [
                    'external_id' => $externalId,
                    'fapshi_user_id' => $fapshiUserId,
                    'link' => $result['link'],
                    'phone' => $data['phone'] ?? null,
                    'note' => $data['reference_note'] ?? null,
                    'initiated_at' => $result['dateInitiated'] ?? null,
                ],
            ]);

            return redirect()->away($result['link']);
        }

        Payment::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'currency' => $wallet->currency,
            'gateway' => $method,
            'reference' => 'DEP-'.strtoupper(Str::random(10)),
            'status' => Payment::STATUS_PENDING,
            'idempotency_key' => $idempotencyKey,
            'payload' => ['phone' => $data['phone'] ?? null, 'note' => $data['reference_note'] ?? null],
        ]);

        return back()->with('status', 'Deposit request submitted. Wallet credit is applied only after verification.');
    }

    private function enabledPaymentMethods(): array
    {
        return array_values((array) app(SettingsService::class)->get('payments.methods', ['mtn_momo', 'orange_money', 'card']));
    }

    private function paymentMethodOptions(): array
    {
        $labels = [
            'mtn_momo' => 'MTN Mobile Money',
            'orange_money' => 'Orange Money',
            'card' => 'International card',
            'bank_transfer' => 'Bank transfer',
            'crypto' => 'Crypto',
        ];

        return collect($this->enabledPaymentMethods())
            ->map(fn (string $method) => ['value' => $method, 'label' => $labels[$method] ?? str_replace('_', ' ', $method)])
            ->values()
            ->all();
    }
}
