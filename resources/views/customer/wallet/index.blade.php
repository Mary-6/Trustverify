@extends('layouts.account')

@section('title', 'Wallet | TrustVerifySMS')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl bg-brand-navy p-8 text-white">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Wallet</p>
        <h1 class="mt-2 text-3xl font-extrabold">{{ number_format((float) $wallet->balance, 0) }} {{ $wallet->currency }}</h1>
        <p class="mt-2 text-slate-300">Available balance for SMM services and verification products.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
        <div id="add-funds" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-extrabold text-brand-navy">Deposit</h2>
            <p class="mt-2 text-sm text-brand-muted">Submit a deposit request. MTN MoMo deposits are credited automatically after payment confirmation.</p>
            @if(! empty($returnTo))
                <div class="mt-4 rounded-xl border border-brand-teal/30 bg-brand-teal-soft p-3 text-sm">
                    <p class="font-extrabold text-brand-navy">Deposit to continue your order.</p>
                    <a href="{{ $returnTo }}" class="mt-2 inline-flex text-sm font-extrabold text-brand-teal hover:text-brand-gold">Return to your order →</a>
                </div>
            @endif

            @if(count($paymentMethods) > 0)
            <form method="POST" action="{{ route('dashboard.wallet.deposit') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $depositKey) }}">
                <input type="hidden" name="return_to" value="{{ $returnTo ?? '' }}">
                <div>
                    <label class="text-sm font-bold text-brand-text" for="amount">Amount ({{ $wallet->currency }})</label>
                    <input id="amount" name="amount" type="number" min="100" step="1" value="{{ old('amount') }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                    @error('amount')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <p class="text-sm font-bold text-brand-text">Payment method</p>
                    <div class="mt-2 space-y-3">
                        @foreach($paymentMethods as $method)
                            <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-brand-teal/40 bg-brand-teal-soft p-4">
                                <span>
                                    <span class="block font-extrabold text-brand-navy">{{ $method['label'] }}</span>
                                    <span class="mt-1 block text-xs font-bold text-brand-teal">Available</span>
                                </span>
                                <input type="radio" name="method" value="{{ $method['value'] }}" class="h-5 w-5 accent-brand-teal" @checked(old('method', 'mtn_momo') === $method['value']) required>
                            </label>
                        @endforeach
                    </div>
                    @error('method')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-bold text-brand-text" for="phone">Phone / account reference</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="MTN MoMo number">
                </div>
                <div>
                    <label class="text-sm font-bold text-brand-text" for="reference_note">Note</label>
                    <textarea id="reference_note" name="reference_note" rows="2" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Payment reference or note">{{ old('reference_note') }}</textarea>
                </div>
                <button class="w-full rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Deposit</button>
            </form>
            @else
                <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-brand-bg p-5 text-sm text-brand-muted">Deposits are temporarily unavailable. Please try again later.</div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Transactions</h2>
                @if($transactions->isEmpty())
                    <p class="mt-3 text-sm text-brand-muted">No wallet transactions yet.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Type</th><th class="pb-3">Amount</th><th class="pb-3">Balance after</th><th class="pb-3">Reference</th><th class="pb-3">Date</th></tr></thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                    <tr class="border-t border-slate-100">
                                        <td class="py-3 font-semibold">{{ str_replace('_', ' ', $tx->type) }}</td>
                                        <td class="py-3">{{ number_format((float) $tx->amount, 0) }} {{ $tx->currency }}</td>
                                        <td class="py-3">{{ number_format((float) $tx->balance_after, 0) }}</td>
                                        <td class="py-3 text-brand-muted">{{ $tx->reference ?: '—' }}</td>
                                        <td class="py-3 text-brand-muted">{{ $tx->created_at->format('M j, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $transactions->links() }}</div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Deposit requests</h2>
                @if($payments->isEmpty())
                    <p class="mt-3 text-sm text-brand-muted">No deposit requests yet.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Reference</th><th class="pb-3">Amount</th><th class="pb-3">Method</th><th class="pb-3">Status</th></tr></thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr class="border-t border-slate-100">
                                        <td class="py-3 font-semibold">{{ $payment->reference }}</td>
                                        <td class="py-3">{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency }}</td>
                                        <td class="py-3">{{ str_replace('_', ' ', $payment->gateway) }}</td>
                                        <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $payment->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $payments->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
