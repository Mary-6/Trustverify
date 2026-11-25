@extends('layouts.account')

@section('title', 'Profile | TrustVerifySMS')

@section('content')
@php
    $user = auth()->user();
    $initials = strtoupper(implode('', array_slice(preg_split('/\s+/', trim($user->name)), 0, 2)));
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl bg-brand-navy p-6 text-white sm:p-8">
        <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-brand-teal/20 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-teal text-xl font-extrabold text-brand-navy">{{ $initials ?: 'TV' }}</span>
                <div class="min-w-0">
                    <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Profile</p>
                    <h1 class="mt-1 truncate text-2xl font-extrabold sm:text-3xl">{{ $user->name }}</h1>
                    <p class="mt-1 truncate text-sm text-slate-300">{{ $user->email }}</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_auto] lg:w-[560px]">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-300">Wallet balance</p>
                    <p class="mt-1 text-2xl font-extrabold">{{ number_format((float) $wallet->balance, 0) }} {{ $wallet->currency }}</p>
                    <p class="mt-1 text-xs text-slate-300">{{ $ordersCount }} total orders</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row lg:flex-col">
                    <a href="{{ route('services.index') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Order</a>
                    <a href="{{ route('dashboard.wallet.index') }}#add-funds" class="inline-flex items-center justify-center rounded-xl border border-white/25 px-5 py-3 text-sm font-extrabold text-white hover:bg-white/10">Deposit</a>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-brand-navy">Recent orders</h2>
                        <p class="mt-1 text-sm text-brand-muted">Track SMM and verification orders from one place.</p>
                    </div>
                    <a href="{{ route('dashboard.orders.index') }}" class="shrink-0 text-sm font-bold text-brand-teal hover:text-brand-gold">View all</a>
                </div>

                @if($recentOrders->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-brand-bg p-6 text-center">
                        <p class="font-bold text-brand-navy">No orders yet</p>
                        <p class="mt-1 text-sm text-brand-muted">Start with an SMM service or SMS verification.</p>
                        <div class="mt-4 flex flex-col justify-center gap-2 sm:flex-row">
                            <a href="{{ route('services.index') }}" class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Browse SMM</a>
                            <a href="{{ route('verification.index') }}" class="rounded-xl bg-brand-teal px-4 py-2 text-sm font-extrabold text-brand-navy">Verify SMS</a>
                        </div>
                    </div>
                @else
                    <div class="mt-5 divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <a href="{{ route('dashboard.orders.show', $order) }}" class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 hover:bg-brand-bg/60 sm:flex-row sm:items-center sm:justify-between sm:px-3 sm:-mx-3">
                                <div class="min-w-0">
                                    <p class="font-extrabold text-brand-navy">{{ $order->order_number }}</p>
                                    <p class="mt-1 truncate text-sm text-brand-muted">{{ Str::limit($order->product_name, 70) }}</p>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="font-bold text-brand-navy">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</span>
                                    <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-xs font-bold text-brand-navy">{{ str_replace('_', ' ', $order->status) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-extrabold text-brand-navy">Account management</h2>
                <p class="mt-1 text-sm text-brand-muted">Manage your profile, wallet, orders and verification history.</p>
                <div class="mt-4 divide-y divide-slate-100">
                    <a href="{{ route('profile') }}" class="group flex items-center justify-between gap-4 py-4 first:pt-0">
                        <span><span class="block font-extrabold text-brand-navy">Profile</span><span class="mt-1 block text-sm text-brand-muted">Update your name, email and password.</span></span>
                        <span class="text-brand-teal transition group-hover:translate-x-1">→</span>
                    </a>
                    <a href="{{ route('verification.index') }}" class="group flex items-center justify-between gap-4 py-4">
                        <span><span class="block font-extrabold text-brand-navy">Verification services</span><span class="mt-1 block text-sm text-brand-muted">Start a new SMS verification session.</span></span>
                        <span class="text-brand-teal transition group-hover:translate-x-1">→</span>
                    </a>
                    <a href="{{ route('dashboard.numbers.index') }}" class="group flex items-center justify-between gap-4 py-4">
                        <span><span class="block font-extrabold text-brand-navy">Verification history</span><span class="mt-1 block text-sm text-brand-muted">Review numbers and received SMS codes.</span></span>
                        <span class="text-brand-teal transition group-hover:translate-x-1">→</span>
                    </a>
                    <a href="{{ route('dashboard.wallet.index') }}" class="group flex items-center justify-between gap-4 py-4">
                        <span><span class="block font-extrabold text-brand-navy">Wallet &amp; payments</span><span class="mt-1 block text-sm text-brand-muted">Balance, deposits and transactions.</span></span>
                        <span class="text-brand-teal transition group-hover:translate-x-1">→</span>
                    </a>
                    <a href="{{ route('dashboard.orders.index') }}" class="group flex items-center justify-between gap-4 py-4 last:pb-0">
                        <span><span class="block font-extrabold text-brand-navy">My orders</span><span class="mt-1 block text-sm text-brand-muted">Search and filter all order history.</span></span>
                        <span class="text-brand-teal transition group-hover:translate-x-1">→</span>
                    </a>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-extrabold text-brand-navy">Referral program</h2>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Share your link. Rewards are applied automatically after a referred customer’s first verified wallet deposit.</p>
                <div class="mt-4 rounded-xl bg-brand-bg p-3 text-sm font-bold text-brand-navy break-all">{{ $referralUrl }}</div>
                <p class="mt-3 text-xs text-brand-muted">Code: {{ $referralCode }} · Referred: {{ $referredCount }} · Rewarded: {{ $rewardedReferrals }}</p>
            </div>

            <div class="rounded-3xl border border-brand-gold/30 bg-brand-gold/10 p-6">
                <h2 class="font-extrabold text-brand-navy">Need help?</h2>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Contact support with your order number or payment reference for help with orders, wallet deposits and verification numbers.</p>
                <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                    <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Contact support</a>
                    <a href="{{ route('support') }}" class="inline-flex items-center justify-center rounded-xl border border-brand-navy/20 px-4 py-2 text-sm font-bold text-brand-navy">Support centre</a>
                </div>
                <div class="mt-5 border-t border-brand-gold/20 pt-4 text-sm">
                    <a href="{{ route('terms') }}" class="font-semibold text-brand-teal hover:text-brand-gold">Terms</a>
                    <span class="mx-2 text-brand-muted">·</span>
                    <a href="{{ route('privacy') }}" class="font-semibold text-brand-teal hover:text-brand-gold">Privacy</a>
                    <span class="mx-2 text-brand-muted">·</span>
                    <a href="{{ route('refund') }}" class="font-semibold text-brand-teal hover:text-brand-gold">Refund policy</a>
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection
