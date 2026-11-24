@extends('layouts.account')

@php
    $delivery = $delivery ?? [];
    $verification = $order->payload['verification'] ?? [];
@endphp

@section('title', 'Verification '.$order->order_number.' | TrustVerifySMS')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ $backUrl }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← Verification services</a>
            <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">Verification {{ $order->order_number }}</h1>
            <p class="mt-1 text-brand-muted">{{ $order->product_name }}</p>
        </div>
        <span id="verification-status-badge" class="rounded-full bg-brand-teal-soft px-4 py-2 text-sm font-extrabold text-brand-navy">{{ str_replace('_', ' ', $order->status) }}</span>
    </div>

    <div class="grid gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-brand-teal bg-brand-teal-soft px-4 py-3 text-sm font-extrabold text-brand-navy">1 · App chosen</div>
        <div class="rounded-2xl border border-brand-teal bg-brand-teal-soft px-4 py-3 text-sm font-extrabold text-brand-navy">2 · Country chosen</div>
        <div class="rounded-2xl border border-brand-teal bg-brand-teal-soft px-4 py-3 text-sm font-extrabold text-brand-navy">3 · Number requested</div>
        <div class="rounded-2xl border border-brand-navy bg-brand-navy px-4 py-3 text-sm font-extrabold text-white">4 · Receive code</div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-extrabold text-brand-navy">Order details</h2>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="font-bold text-brand-muted">App</dt><dd class="mt-1 font-semibold text-brand-text">{{ $verification['app'] ?? '—' }}</dd></div>
                    <div><dt class="font-bold text-brand-muted">Country</dt><dd class="mt-1 font-semibold text-brand-text">{{ $verification['country'] ?? '—' }}</dd></div>
                    <div><dt class="font-bold text-brand-muted">Price</dt><dd class="mt-1 font-semibold text-brand-text">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</dd></div>
                    <div><dt class="font-bold text-brand-muted">Provider status</dt><dd class="mt-1 font-semibold text-brand-text" id="verification-provider-status">{{ $order->supplier_status ?: 'Waiting for provider' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-extrabold text-brand-navy">Verification code</h2>
                <div id="verification-code-state" class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-brand-bg p-6 text-center">
                    <p class="text-sm font-bold uppercase tracking-wider text-brand-teal" id="verification-state-label">Waiting for SMS</p>
                    <p class="mt-2 text-sm text-brand-muted" id="verification-state-message">We are checking the provider for the verification SMS. Keep this page open.</p>
                    <div id="verification-code-box" class="mt-5 hidden">
                        <p class="text-3xl font-extrabold tracking-widest text-brand-navy" id="verification-code">—</p>
                        <button type="button" id="verification-copy-code" class="mt-4 rounded-xl bg-brand-navy px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-navy-light">Copy code</button>
                    </div>
                </div>
                <p class="mt-4 text-xs text-brand-muted">Codes are provided by the verification provider only. TrustVerifySMS does not generate or display sample codes.</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-extrabold text-brand-navy">Your number</h2>
                <p id="verification-number" class="mt-4 break-all text-2xl font-extrabold text-brand-navy">{{ $delivery['number'] ?? 'Waiting for provider' }}</p>
                <p class="mt-2 text-sm text-brand-muted" id="verification-ends-at">{{ ! empty($delivery['ends_at']) ? 'Valid until '.\Illuminate\Support\Carbon::parse($delivery['ends_at'])->diffForHumans() : 'Availability is confirmed by the provider.' }}</p>
                <div class="mt-5 flex flex-col gap-2">
                    @if($canCancel)
                        <form method="POST" action="{{ $cancelUrl }}">
                            @csrf
                            <button class="w-full rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-extrabold text-red-700 hover:bg-red-100">Cancel verification</button>
                        </form>
                    @else
                        <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-brand-muted">Cancellation is only available before the number is submitted to the provider. Once submitted, provider rules apply.</p>
                    @endif
                    <a href="{{ route('dashboard.orders.index') }}" class="rounded-xl bg-brand-navy px-4 py-3 text-center text-sm font-extrabold text-white hover:bg-brand-navy-light">View in My Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const statusUrl = @js($statusUrl);
    const initialTerminal = @js($order->isTerminal());
    const state = { terminal: initialTerminal };

    function badge(status) {
        const el = document.getElementById('verification-status-badge');
        el.textContent = String(status || 'pending').replace(/_/g, ' ');
    }

    function labelFor(data) {
        const raw = String(data.state || data.supplier_status || data.status || '').toLowerCase();
        if (raw.includes('timedout') || raw.includes('timed_out') || raw.includes('expired')) return 'Expired';
        if (data.status === 'completed') return 'Completed';
        if (data.status === 'cancelled') return 'Cancelled';
        if (data.status === 'failed' || data.status === 'refunded') return 'Failed';
        if (data.sms_code || data.sms_content) return 'Code Received';
        return 'Waiting for SMS';
    }

    function render(data) {
        badge(data.status);
        document.getElementById('verification-provider-status').textContent = data.supplier_status || 'Waiting for provider';
        if (data.number) document.getElementById('verification-number').textContent = data.number;
        const code = data.sms_code || data.sms_content || '';
        const stateLabel = labelFor(data);
        document.getElementById('verification-state-label').textContent = stateLabel;
        document.getElementById('verification-state-message').textContent = stateLabel === 'Code Received'
            ? 'The verification SMS has been received from the provider.'
            : stateLabel === 'Completed' ? 'This verification is complete.' : stateLabel === 'Cancelled' ? 'This verification was cancelled.' : stateLabel === 'Expired' ? 'This verification expired before the SMS arrived.' : stateLabel === 'Failed' ? 'The provider could not complete this verification.' : 'We are checking the provider for the verification SMS. Keep this page open.';
        if (code) {
            document.getElementById('verification-code').textContent = code;
            document.getElementById('verification-code-box').classList.remove('hidden');
        }
        state.terminal = !!data.terminal;
    }

    async function poll() {
        if (state.terminal) return;
        try {
            const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (response.ok) render(await response.json());
        } catch (error) {
            // Keep the last known state and retry on the next interval.
        }
    }

    document.getElementById('verification-copy-code').addEventListener('click', async function () {
        const code = document.getElementById('verification-code').textContent.trim();
        if (!code || code === '—') return;
        try {
            await navigator.clipboard.writeText(code);
            this.textContent = 'Copied';
            setTimeout(() => { this.textContent = 'Copy code'; }, 1500);
        } catch (error) {
            window.prompt('Copy this code', code);
        }
    });

    if (!state.terminal) {
        setInterval(poll, 5000);
        poll();
    }
})();
</script>
@endsection
