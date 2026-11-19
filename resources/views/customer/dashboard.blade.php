@extends('layouts.account')

@section('title', 'Dashboard | Trustverifysms')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl bg-brand-navy p-8 text-white">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Welcome back</p>
        <h1 class="mt-2 text-3xl font-extrabold">{{ auth()->user()->name }}</h1>
        <p class="mt-2 text-slate-300">Manage orders, wallet funds, virtual numbers, API access and support from one place.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-brand-muted">Wallet balance</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format((float) $wallet->balance, 0) }} {{ $wallet->currency }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-brand-muted">Orders</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ $ordersCount }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-brand-muted">Active numbers</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ $activeNumbers }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-brand-muted">Open tickets</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ $openTickets }}</p></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-extrabold text-brand-navy">Recent orders</h2>
                <a href="{{ route('dashboard.orders.index') }}" class="text-sm font-bold text-brand-teal">View all</a>
            </div>
            @if($recentOrders->isEmpty())
                <p class="mt-4 text-sm text-brand-muted">No orders yet. <a class="font-bold text-brand-teal" href="{{ route('services.index') }}">Browse services</a>.</p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Order</th><th class="pb-3">Product</th><th class="pb-3">Total</th><th class="pb-3">Status</th></tr></thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr class="border-t border-slate-100">
                                    <td class="py-3 font-semibold"><a class="text-brand-teal" href="{{ route('dashboard.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                    <td class="py-3">{{ Str::limit($order->product_name, 40) }}</td>
                                    <td class="py-3 font-semibold">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</td>
                                    <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $order->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Quick actions</h2>
                <div class="mt-4 grid gap-3">
                    <a href="{{ route('services.index') }}" class="rounded-xl bg-brand-teal px-4 py-3 text-center text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Buy services</a>
                    <a href="{{ route('dashboard.wallet.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-bold text-brand-navy hover:bg-slate-50">Wallet & deposits</a>
                    <a href="{{ route('dashboard.api-keys.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-bold text-brand-navy hover:bg-slate-50">API keys</a>
                </div>
            </div>
            <div class="rounded-2xl border border-brand-gold/30 bg-brand-gold/10 p-6">
                <h2 class="font-extrabold text-brand-navy">Need help?</h2>
                <p class="mt-2 text-sm text-brand-muted">Open a support ticket with your order number or payment reference.</p>
                <a href="{{ route('dashboard.tickets.index') }}" class="mt-4 inline-flex rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Support tickets</a>
            </div>
        </div>
    </div>
</div>
@endsection
