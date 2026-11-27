<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\FapshiGateway;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['user', 'wallet'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function verify(Request $request, Payment $payment, WalletService $wallets, FapshiGateway $fapshi, ReferralService $referrals)
    {
        abort_unless($payment->status === Payment::STATUS_PENDING, 422, 'Only pending payments can be verified.');
        abort_unless($payment->gateway === 'fapshi', 422, 'Manual payment verification is disabled. Only Fapshi payments can be synced.');
        abort_unless($payment->reference, 422, 'Fapshi payment has no transaction reference.');

        $status = $fapshi->paymentStatus($payment->reference);
        $fapshiStatus = strtoupper((string) ($status['status'] ?? ''));

        if (in_array($fapshiStatus, ['FAILED', 'EXPIRED'], true)) {
            $payment->forceFill(['status' => Payment::STATUS_FAILED])->save();

            return back()->with('status', 'Fapshi payment marked as failed.');
        }

        abort_unless($fapshiStatus === 'SUCCESSFUL', 422, 'Fapshi payment is not successful yet.');
        abort_unless((int) ($status['amount'] ?? 0) === (int) $payment->amount, 422, 'Fapshi payment amount mismatch.');

        $paymentExternalId = (string) ($payment->payload['external_id'] ?? '');
        $verifiedExternalId = (string) ($status['externalId'] ?? '');
        abort_unless($paymentExternalId === '' || $verifiedExternalId === $paymentExternalId, 422, 'Fapshi payment reference mismatch.');

        DB::transaction(function () use ($payment, $wallets, $referrals, $request, $status) {
            $payment->forceFill([
                'status' => Payment::STATUS_SUCCESS,
                'verified_at' => now(),
                'payload' => array_merge($payment->payload ?? [], ['verified_transaction' => $status]),
            ])->save();

            $wallets->credit($payment->user, $payment->amount, 'deposit', [
                'idempotency_key' => 'payment-'.$payment->id,
                'reference' => $payment->reference,
                'gateway' => $payment->gateway,
                'metadata' => ['payment_id' => $payment->id],
                'created_by' => $request->user()->id,
            ]);

            $referrals->rewardFirstDeposit($payment->user);
        });

        return back()->with('status', 'Fapshi payment verified and wallet credited.');
    }

    public function reject(Request $request, Payment $payment)
    {
        abort_unless($payment->status === Payment::STATUS_PENDING, 422, 'Only pending payments can be rejected.');
        abort_unless($payment->gateway !== 'fapshi', 422, 'Fapshi payments are updated by webhook/status sync only.');

        $payment->forceFill(['status' => Payment::STATUS_FAILED])->save();

        return back()->with('status', 'Payment rejected.');
    }
}
