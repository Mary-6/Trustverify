@extends('layouts.site')

@section('title', 'Pricing | TrustVerifySMS')
@section('description', 'Transparent pricing, wallet funding and marketplace fee information for TrustVerifySMS SMM services and virtual numbers.')

@section('content')
<section class="relative overflow-hidden bg-brand-navy text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #06B6D4 0, transparent 32%), radial-gradient(circle at 80% 10%, #F59E0B 0, transparent 24%);"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Pricing</p>
        <h1 class="mt-3 max-w-3xl text-4xl font-extrabold sm:text-5xl">Simple, transparent marketplace pricing.</h1>
        <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-300">Every product shows its retail price before checkout. Wallet deposits are verified before credit, and order limits are shown on each service page.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft text-brand-navy"><span class="text-lg font-extrabold">1</span></div>
            <h2 class="mt-5 text-xl font-extrabold text-brand-navy">Product pricing</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">SMM prices are shown in XAF, including per-1,000 pricing where applicable. Virtual-number products show their configured XAF price before purchase.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft text-brand-navy"><span class="text-lg font-extrabold">2</span></div>
            <h2 class="mt-5 text-xl font-extrabold text-brand-navy">Wallet balance</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Orders are paid from your wallet after balance validation. Deposits are credited only after payment verification.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft text-brand-navy"><span class="text-lg font-extrabold">3</span></div>
            <h2 class="mt-5 text-xl font-extrabold text-brand-navy">What you see first</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Minimum/maximum order, availability, supplier mode and delivery expectations are shown before you commit.</p>
        </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-extrabold text-brand-navy">Before you order</h2>
            <ul class="mt-5 space-y-3 text-sm text-brand-muted">
                <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Check the service type, brand and quality option.</li>
                <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Confirm quantity against minimum and maximum limits.</li>
                <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Review the exact XAF total on the checkout form.</li>
                <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Keep your order ID for tracking and support.</li>
            </ul>
        </div>
        <div class="rounded-3xl bg-brand-navy p-8 text-white">
            <h2 class="text-2xl font-extrabold">Need a custom setup?</h2>
            <p class="mt-3 text-sm leading-7 text-slate-300">Use the developer API for products, orders and wallet automation, or contact support for account and payment questions.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('api.docs') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">API docs</a>
                <a href="{{ route('support') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 px-5 py-3 text-sm font-bold text-white hover:bg-white/10">Support</a>
            </div>
        </div>
    </div>
</section>
@endsection
