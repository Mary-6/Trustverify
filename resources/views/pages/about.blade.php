@extends('layouts.site')

@section('title', 'About TrustVerifySMS')
@section('description', 'Learn how TrustVerifySMS helps customers grow social channels and verify accounts with SMM services, SMS verification and wallet checkout.')

@section('content')
<section class="bg-gradient-to-br from-brand-navy via-brand-navy-light to-brand-teal text-white">
    <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-sm font-extrabold uppercase tracking-[0.24em] text-brand-gold">About us</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-5xl">Built for customers who need fast, clear digital services.</h1>
        <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-200">TrustVerifySMS is a digital services marketplace for SMM growth and SMS verification. We keep ordering simple: choose a service, pay from your wallet, track the order and contact support when you need help.</p>
    </div>
</section>

<section class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-brand-navy">SMM services</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Followers, likes, views, subscribers, reactions and engagement tools across major social platforms.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-brand-navy">Verification services</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">SMS verification products that help customers receive one-time codes for supported platforms.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-brand-navy">Wallet checkout</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Top up once with MTN Mobile Money and use your wallet balance for orders, with transaction history kept in your account.</p>
        </div>
    </div>
</section>

<section class="border-t border-slate-200 bg-brand-bg">
    <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-black text-brand-navy">How we work</h2>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft font-extrabold text-brand-navy">1</div>
                <h3 class="mt-5 text-lg font-extrabold text-brand-navy">Create an account</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Register with your email, fund your wallet and keep your order history in one place.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft font-extrabold text-brand-navy">2</div>
                <h3 class="mt-5 text-lg font-extrabold text-brand-navy">Choose a service</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Browse SMM or verification services, check the product details and submit the required information.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft font-extrabold text-brand-navy">3</div>
                <h3 class="mt-5 text-lg font-extrabold text-brand-navy">Track your order</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Follow status updates, review delivery and contact support if an order needs attention.</p>
            </div>
        </div>
    </div>
</section>
@endsection
