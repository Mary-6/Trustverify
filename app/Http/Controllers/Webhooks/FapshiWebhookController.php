<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\FapshiGateway;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FapshiWebhookController extends Controller
{
    public function handle(Request $request, FapshiGateway $fapshi, WalletService $wallets)
    {
        $secret = (string) config('fapshi.webhook_secret', '');
        $provided = (string) $request->header('x-wh-secret', '');

        if ($secret === '' || $provided === '' || ! hash_equals($secret, $provided)) {
            return response()->json(['message' => 'Invalid webhook secret.'], 403);
        }

        $transaction = $request->json()->all();
        $transId = $transaction['transId'] ?? null;

        if (! is_string($transId) || $transId === '') {
            return response()->json(['message' => 'Missing transaction id.'], 422);
        }

        $payment = Payment::where('gateway', 'fapshi')->where('reference', $transId)->first();
        $externalId = $transaction['externalId'] ?? null;

        if (! $payment && is_string($externalId) && $externalId !== '') {
            $payment = Payment::where('gateway', 'fapshi')->where('payload->external_id', $externalId)->first();
        }

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return response()->json(['message' => 'ok']);
        }

        try {
            $verified = $fapshi->paymentStatus($transId);
        } catch (\Throwable) {
            return response()->json(['message' => 'Status verification unavailable.'], 503);
        }

        $status = strtoupper((string) ($verified['status'] ?? ''));

        if ($status === 'SUCCESSFUL') {
            $amount = (int) ($verified['amount'] ?? 0);
            $verifiedExternalId = (string) ($verified['externalId'] ?? '');
            $paymentExternalId = (string) ($payment->payload['external_id'] ?? '');

            if ($amount !== (int) $payment->amount || ($paymentExternalId !== '' && $verifiedExternalId !== $paymentExternalId)) {
                return response()->json(['message' => 'Payment verification mismatch.'], 422);
            }

            if ($payment->status === Payment::STATUS_PENDING) {
                DB::transaction(function () use ($payment, $wallets, $transId, $verified) {
                    $payment->forceFill([
                        'status' => Payment::STATUS_SUCCESS,
                        'verified_at' => now(),
                        'payload' => array_merge($payment->payload ?? [], ['verified_transaction' => $verified]),
                    ])->save();

                    $wallets->credit($payment->user, $payment->amount, 'deposit', [
                        'idempotency_key' => 'payment-'.$payment->id,
                        'reference' => $payment->reference,
                        'gateway' => 'fapshi',
                        'metadata' => ['payment_id' => $payment->id, 'trans_id' => $transId],
                    ]);
                });
            }

            return response()->json(['message' => 'ok']);
        }

        if (in_array($status, ['FAILED', 'EXPIRED'], true) && $payment->status === Payment::STATUS_PENDING) {
            $payment->forceFill(['status' => Payment::STATUS_FAILED])->save();
        }

        return response()->json(['message' => 'ok']);
    }
}
