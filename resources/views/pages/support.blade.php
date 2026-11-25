@extends('layouts.site')

@section('title', 'Support | TrustVerifySMS')
@section('description', 'Get help with TrustVerifySMS orders, wallet deposits, SMM services and virtual numbers.')

@section('content')
<section class="relative overflow-hidden bg-brand-navy text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #13B89A 0, transparent 32%), radial-gradient(circle at 80% 10%, #B6F36B 0, transparent 24%);"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Support</p>
        <h1 class="mt-3 max-w-3xl text-4xl font-extrabold sm:text-5xl">Get a straight answer.</h1>
        <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-300">Use support tickets for orders, payments, delivery and account questions. Keep your order ID or payment reference ready so support can find the activity quickly.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-extrabold text-brand-navy">What support can help with</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-brand-bg p-5"><h3 class="font-bold text-brand-navy">Orders</h3><p class="mt-2 text-sm text-brand-muted">Status, delivery timing, link/details issues and supplier delays.</p></div>
                <div class="rounded-2xl bg-brand-bg p-5"><h3 class="font-bold text-brand-navy">Wallet</h3><p class="mt-2 text-sm text-brand-muted">Deposit verification, payment references, balances and transaction history.</p></div>
                <div class="rounded-2xl bg-brand-bg p-5"><h3 class="font-bold text-brand-navy">Virtual numbers</h3><p class="mt-2 text-sm text-brand-muted">SMS retrieval, expiry, service selection and activation questions.</p></div>
            </div>
            @auth
                <a href="{{ route('dashboard.tickets.index') }}" class="mt-8 inline-flex rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Open support tickets</a>
            @else
                <a href="{{ route('login') }}" class="mt-8 inline-flex rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Sign in for support</a>
                <a href="{{ route('register') }}" class="ml-0 mt-3 inline-flex rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-brand-navy hover:bg-slate-50 sm:ml-3 sm:mt-8">Create account</a>
            @endauth
        </div>

        <aside class="space-y-6">
            @php($supportEmail = app(\App\Services\SettingsService::class)->get('general.support_email', 'support@trustverifysms.com'))
            <div class="rounded-3xl bg-brand-navy p-7 text-white">
                <h2 class="text-xl font-extrabold">Before contacting support</h2>
                <ul class="mt-4 space-y-3 text-sm text-slate-300">
                    <li class="flex gap-3"><span class="text-brand-teal">✓</span> Have your order number ready.</li>
                    <li class="flex gap-3"><span class="text-brand-teal">✓</span> For wallet deposits, include the payment reference.</li>
                    <li class="flex gap-3"><span class="text-brand-teal">✓</span> For SMS issues, confirm the virtual number is still active.</li>
                </ul>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="text-xl font-extrabold text-brand-navy">Company email</h2>
                <p class="mt-3 text-sm text-brand-muted">For account, billing and order questions, email <a class="font-bold text-brand-teal hover:text-brand-gold" href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.</p>
                <p class="mt-2 text-xs text-brand-muted">For faster help, open a ticket and include your order or payment reference.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="text-xl font-extrabold text-brand-navy">Account safety</h2>
                <ul class="mt-4 space-y-3 text-sm text-brand-muted">
                    <li>Do not send passwords, API keys or payment card details.</li>
                    <li>Use the password reset link if you cannot sign in.</li>
                    <li>Keep API keys private and revoke unused keys.</li>
                </ul>
            </div>
        </aside>
    </div>
</section>
@endsection
