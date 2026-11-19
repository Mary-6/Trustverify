@extends('layouts.site')

@section('title', 'Support | Trustverifysms')
@section('description', 'Get help with Trustverifysms orders, wallet deposits, SMM services and virtual numbers.')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-3xl font-extrabold text-brand-navy">How can we help?</h1>
            <p class="mt-3 text-brand-muted">Use support tickets for order, payment, delivery and account questions. Do not send passwords or payment card details.</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-brand-bg p-4"><h2 class="font-bold text-brand-navy">Orders</h2><p class="mt-1 text-sm text-brand-muted">Status, delivery and supplier delays.</p></div>
                <div class="rounded-2xl bg-brand-bg p-4"><h2 class="font-bold text-brand-navy">Wallet</h2><p class="mt-1 text-sm text-brand-muted">Deposits, verification and refunds.</p></div>
                <div class="rounded-2xl bg-brand-bg p-4"><h2 class="font-bold text-brand-navy">Virtual numbers</h2><p class="mt-1 text-sm text-brand-muted">SMS retrieval, expiry and activation.</p></div>
            </div>
            @auth
                <a href="{{ route('dashboard.tickets.index') }}" class="mt-8 inline-flex rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Open support tickets</a>
            @else
                <a href="{{ route('login') }}" class="mt-8 inline-flex rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Sign in for support</a>
            @endauth
        </div>
        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-extrabold text-brand-navy">Before contacting support</h2>
            <ul class="mt-4 space-y-3 text-sm text-brand-muted">
                <li>Have your order number ready.</li>
                <li>For wallet deposits, include the payment reference.</li>
                <li>For SMS issues, confirm the virtual number is still active.</li>
            </ul>
        </aside>
    </div>
</section>
@endsection
