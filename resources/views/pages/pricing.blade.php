@extends('layouts.site')

@section('title', 'Pricing | Trustverifysms')
@section('description', 'Transparent pricing, wallet funding and marketplace fee information for Trustverifysms SMM services and virtual numbers.')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Pricing</p>
    <h1 class="mt-2 text-4xl font-extrabold text-brand-navy">Simple, transparent marketplace pricing.</h1>
    <p class="mt-4 max-w-3xl text-brand-muted">Product prices are set per catalogue item. Wallet deposits are verified before credit. Currency conversion and gateway fees may apply depending on the payment method.</p>
    <div class="mt-10 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6"><h2 class="font-extrabold text-brand-navy">Product pricing</h2><p class="mt-2 text-sm text-brand-muted">Each product displays its configured retail price before checkout.</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6"><h2 class="font-extrabold text-brand-navy">Wallet balance</h2><p class="mt-2 text-sm text-brand-muted">Orders are paid from your wallet after balance validation.</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6"><h2 class="font-extrabold text-brand-navy">Admin controls</h2><p class="mt-2 text-sm text-brand-muted">Markup, visibility, supplier mappings and payment settings are managed by administrators.</p></div>
    </div>
</section>
@endsection
