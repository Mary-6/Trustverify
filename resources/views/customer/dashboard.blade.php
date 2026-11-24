@extends('layouts.account')

@section('title', 'Account | TrustVerifySMS')

@section('content')
@php
    $user = auth()->user();
    $initials = strtoupper(implode('', array_slice(preg_split('/\s+/', trim($user->name)), 0, 2)));
@endphp

<div class="space-y-6">
    <div class="rounded-3xl bg-brand-navy p-8 text-white">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-teal text-xl font-extrabold text-brand-navy">{{ $initials ?: 'TV' }}</span>
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Account</p>
                    <h1 class="mt-1 text-2xl font-extrabold sm:text-3xl">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-slate-300">{{ $user->email }}</p>
                </div>
            </div>
            <a href="{{ route('services.index') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Order</a>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-semibold text-brand-muted">Wallet balance</p>
            <p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format((float) $wallet->balance, 0) }} {{ $wallet->currency }}</p>
            <a href="{{ route('dashboard.wallet.index') }}#add-funds" class="mt-3 inline-flex text-sm font-bold text-brand-teal hover:text-brand-gold">Add Money →</a>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-brand-muted">Orders</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ $ordersCount }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-brand-muted">Verification numbers</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ $activeNumbers }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-brand-muted">Open tickets</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ $openTickets }}</p></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-brand-navy">Recent orders</h2>
                    <a href="{{ route('dashboard.orders.index') }}" class="text-sm font-bold text-brand-teal">View all</a>
                </div>
                @if($recentOrders->isEmpty())
                    <p class="mt-4 text-sm text-brand-muted">No orders yet. <a class="font-bold text-brand-teal" href="{{ route('services.index') }}">Choose a service</a>.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach($recentOrders as $order)
                            <a href="{{ route('dashboard.orders.show', $order) }}" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-brand-bg p-4 sm:flex-row sm:items-center sm:justify-between hover:border-brand-teal/50">
                                <div class="min-w-0">
                                    <p class="font-extrabold text-brand-navy">{{ $order->order_number }}</p>
                                    <p class="mt-1 truncate text-sm text-brand-muted">{{ Str::limit($order->product_name, 60) }}</p>
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

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-xl font-extrabold text-brand-navy">Account settings</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('profile') }}" class="rounded-2xl border border-slate-200 bg-brand-bg p-4 hover:border-brand-teal/50"><p class="font-extrabold text-brand-navy">Profile</p><p class="mt-1 text-sm text-brand-muted">Name, email and password.</p></a>
                    <a href="{{ route('dashboard.api-keys.index') }}" class="rounded-2xl border border-slate-200 bg-brand-bg p-4 hover:border-brand-teal/50"><p class="font-extrabold text-brand-navy">API for resellers</p><p class="mt-1 text-sm text-brand-muted">Create and revoke API keys.</p></a>
                    <a href="{{ route('dashboard.numbers.index') }}" class="rounded-2xl border border-slate-200 bg-brand-bg p-4 hover:border-brand-teal/50"><p class="font-extrabold text-brand-navy">Verification services</p><p class="mt-1 text-sm text-brand-muted">View numbers and SMS codes.</p></a>
                    <a href="{{ route('dashboard.tickets.index') }}" class="rounded-2xl border border-slate-200 bg-brand-bg p-4 hover:border-brand-teal/50"><p class="font-extrabold text-brand-navy">Support tickets</p><p class="mt-1 text-sm text-brand-muted">Get help with orders and payments.</p></a>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Referral program</h2>
                <p class="mt-2 text-sm text-brand-muted">Share your link. When a referred customer completes their first wallet deposit, your reward is added automatically if an admin has set a reward amount.</p>
                <div class="mt-4 rounded-xl bg-brand-bg p-3 text-sm font-bold text-brand-navy break-all">{{ $referralUrl }}</div>
                <p class="mt-3 text-xs text-brand-muted">Code: {{ $referralCode }} · Referred: {{ $referredCount }} · Rewarded: {{ $rewardedReferrals }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Quick links</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a class="font-semibold text-brand-teal hover:text-brand-gold" href="{{ url('/').'#faq' }}">Help & FAQ</a></li>
                    <li><a class="font-semibold text-brand-teal hover:text-brand-gold" href="{{ route('terms') }}">Terms of Service</a></li>
                    <li><a class="font-semibold text-brand-teal hover:text-brand-gold" href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a class="font-semibold text-brand-teal hover:text-brand-gold" href="{{ route('refund') }}">Refund Policy</a></li>
                    <li><a class="font-semibold text-brand-teal hover:text-brand-gold" href="{{ route('api.docs') }}">Developer API docs</a></li>
                </ul>
            </div>
            <div class="rounded-2xl border border-brand-gold/30 bg-brand-gold/10 p-6">
                <h2 class="font-extrabold text-brand-navy">Need help?</h2>
                <p class="mt-2 text-sm text-brand-muted">Open a support ticket with your order number or payment reference.</p>
                <a href="{{ route('dashboard.tickets.index') }}" class="mt-4 inline-flex rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Support tickets</a>
            </div>
        </aside>
    </div>
</div>
@endsection
