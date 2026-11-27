<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\FapshiGateway;
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
            'returnTo' => $this->safeReturnTo($request->query('return_to')),
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
            'return_to' => ['nullable', 'string', 'max:2048'],
            'idempotency_key' => ['nullable', 'string', 'max:80'],
        ]);

        $user = $request->user();
        $wallet = app(WalletService::class)->walletFor($user);
        $amount = (int) $data['amount'];
        $method = $data['method'];
        $returnTo = $this->safeReturnTo($data['return_to'] ?? null);
        $useFapshi = $fapshi->isConfigured();
        $idempotencyKey = 'deposit-'.$user->id.'-'.($data['idempotency_key'] ?: (string) Str::uuid());

        $existing = Payment::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            $link = $existing->payload['link'] ?? null;
            if ($useFapshi && $existing->status === Payment::STATUS_PENDING && is_string($link) && $link !== '') {
                return redirect()->away($link);
            }

            return back()->with('status', 'Deposit request already submitted.');
        }

        if (! $useFapshi) {
            return back()->with('error', 'Deposits are temporarily unavailable. Please try again later.')->withInput();
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
                    $this->walletUrl($returnTo),
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
                    'return_to' => $returnTo,
                    'initiated_at' => $result['dateInitiated'] ?? null,
                ],
            ]);

            return redirect()->away($result['link']);
        }

    }

    private function safeReturnTo(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || strlen($value) > 2048 || ! str_starts_with($value, '/') || str_starts_with($value, '//') || str_contains($value, '://') || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            return null;
        }

        return $value;
    }

    private function walletUrl(?string $returnTo): string
    {
        return $returnTo
            ? route('dashboard.wallet.index', ['return_to' => $returnTo])
            : route('dashboard.wallet.index');
    }

    private function enabledPaymentMethods(): array
    {
        return ['mtn_momo'];
    }

    private function paymentMethodOptions(): array
    {
        if (! app(FapshiGateway::class)->isConfigured()) {
            return [];
        }

        $labels = [
            'mtn_momo' => 'MTN Mobile Money',
            'orange_money' => 'Orange Money',
            'card' => 'International card',
            'bank_transfer' => 'Bank transfer',
            'crypto' => 'Crypto',
            'paypal' => 'PayPal',
            'other' => 'Other payment method',
        ];

        return collect($this->enabledPaymentMethods())
            ->map(fn (string $method) => ['value' => $method, 'label' => $labels[$method] ?? str_replace('_', ' ', $method)])
            ->values()
            ->all();
    }
}
