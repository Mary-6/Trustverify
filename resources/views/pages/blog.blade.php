@extends('layouts.site')

@section('title', 'Blog & Guides | TrustVerifySMS')
@section('description', 'TrustVerifySMS guides for SMM services, virtual numbers, wallet funding and API automation.')

@section('content')
<section class="relative overflow-hidden bg-brand-navy text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #13B89A 0, transparent 32%), radial-gradient(circle at 80% 10%, #B6F36B 0, transparent 24%);"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Blog & guides</p>
        <h1 class="mt-3 max-w-3xl text-4xl font-extrabold sm:text-5xl">Practical guides for SMM and virtual numbers.</h1>
        <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-300">Short, honest guides for choosing services, funding your wallet, receiving SMS codes and tracking orders.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">SMM</p>
            <h2 class="mt-3 text-xl font-extrabold text-brand-navy">How to choose an SMM service</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Pick a platform, choose the service group, compare verified options and check the price before checkout.</p>
            <p class="mt-5 text-sm font-bold text-brand-teal">Open SMM services →</p>
        </a>
        <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-gold">Virtual numbers</p>
            <h2 class="mt-3 text-xl font-extrabold text-brand-navy">How SMS verification works</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Buy a verified number, use it on the supported platform, then watch for the SMS code in your order.</p>
            <p class="mt-5 text-sm font-bold text-brand-teal">View virtual numbers →</p>
        </a>
        <a href="{{ route('dashboard.wallet.index') }}" class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Wallet</p>
            <h2 class="mt-3 text-xl font-extrabold text-brand-navy">Funding and tracking orders</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Add funds once, then spend from one balance. Every order and payment stays in your account history.</p>
            <p class="mt-5 text-sm font-bold text-brand-teal">Open wallet →</p>
        </a>
        <a href="{{ route('api.docs') }}" class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-gold">Developers</p>
            <h2 class="mt-3 text-xl font-extrabold text-brand-navy">Automate with the API</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Use API keys for implemented product, order and wallet endpoints from your own systems.</p>
            <p class="mt-5 text-sm font-bold text-brand-teal">Read API docs →</p>
        </a>
    </div>

    <div class="mt-12 rounded-3xl border border-slate-200 bg-brand-bg p-8">
        <h2 class="text-2xl font-extrabold text-brand-navy">Need a human?</h2>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-brand-muted">Open a support ticket with your order ID or payment reference. Guides here are general; your account dashboard has the exact status for each order.</p>
        <a href="{{ route('support') }}" class="mt-5 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Go to support</a>
    </div>
</section>
@endsection
