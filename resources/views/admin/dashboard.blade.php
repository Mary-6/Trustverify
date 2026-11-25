@extends('layouts.admin')

@section('title', 'Admin Dashboard | TrustVerifySMS')

@section('content')
@php
    $statusStyles = [
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200',
        'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-200',
        'submitted' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-200',
        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200',
        'failed' => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200',
        'cancelled' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
        'refunded' => 'bg-purple-100 text-purple-800 dark:bg-purple-500/15 dark:text-purple-200',
    ];
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] bg-brand-navy p-6 text-white shadow-xl sm:p-8">
        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-brand-teal/20 blur-3xl"></div>
        <div class="absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-brand-gold/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-teal">Administration</p>
                <h1 class="mt-3 text-3xl font-extrabold sm:text-4xl">Admin overview</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
                    Monitor revenue, orders, payments, products and support activity from one operational dashboard.
                </p>
                <p class="mt-4 text-sm font-semibold text-slate-300">{{ now()->format('l, j F Y') }}</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.payments.index') }}" class="rounded-xl bg-brand-teal px-4 py-3 text-sm font-extrabold text-brand-navy shadow-lg transition hover:-translate-y-0.5 hover:bg-cyan-300">
                    Review payments
                </a>
                <a href="{{ route('admin.products.create') }}" class="rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-extrabold text-white backdrop-blur transition hover:bg-white/15">
                    Add product
                </a>
                <a href="{{ route('admin.orders.index') }}" class="rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-extrabold text-white backdrop-blur transition hover:bg-white/15">
                    View orders
                </a>
            </div>
        </div>
    </section>

    <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.orders.index') }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-brand-teal/50 hover:shadow-xl dark:border-slate-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-brand-muted">Revenue</p>
                    <p class="mt-3 text-3xl font-extrabold tracking-tight text-brand-navy dark:text-white">{{ number_format((float) $stats['revenue'], 0) }}</p>
                    <p class="mt-1 text-sm font-semibold text-brand-teal">{{ $stats['currency'] }} · non-cancelled orders</p>
                </div>
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-teal-soft text-brand-teal">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13h2v6H3zM8 10h2v9H8zM13 5h2v14h-2zM18 8h2v11h-2z"/></svg>
                </span>
            </div>
            <p class="mt-5 text-sm font-bold text-brand-muted transition group-hover:text-brand-teal">Open orders →</p>
        </a>

        <a href="{{ route('admin.orders.index') }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-brand-teal/50 hover:shadow-xl dark:border-slate-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-brand-muted">Total orders</p>
                    <p class="mt-3 text-3xl font-extrabold tracking-tight text-brand-navy dark:text-white">{{ number_format($stats['orders']) }}</p>
                    <p class="mt-1 text-sm font-semibold text-brand-muted">All customer orders</p>
                </div>
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12L5 3H2"/><path d="M9 20a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM18 20a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
                </span>
            </div>
            <p class="mt-5 text-sm font-bold text-brand-muted transition group-hover:text-brand-teal">Open orders →</p>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl dark:border-slate-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-brand-muted">Pending orders</p>
                    <p class="mt-3 text-3xl font-extrabold tracking-tight text-brand-navy dark:text-white">{{ number_format($stats['pending_orders']) }}</p>
                    <p class="mt-1 text-sm font-semibold text-brand-muted">Awaiting fulfilment</p>
                </div>
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </span>
            </div>
            <p class="mt-5 text-sm font-bold text-brand-muted transition group-hover:text-amber-600">Review pending →</p>
        </a>

        <a href="{{ route('admin.payments.index') }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-brand-teal/50 hover:shadow-xl dark:border-slate-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-brand-muted">Pending payments</p>
                    <p class="mt-3 text-3xl font-extrabold tracking-tight text-brand-navy dark:text-white">{{ number_format($stats['pending_payments']) }}</p>
                    <p class="mt-1 text-sm font-semibold text-brand-muted">Deposits to verify</p>
                </div>
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                </span>
            </div>
            <p class="mt-5 text-sm font-bold text-brand-muted transition group-hover:text-brand-teal">Review payments →</p>
        </a>
    </section>

    <section class="space-y-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Suppliers</p>
                <h2 class="text-2xl font-extrabold text-brand-navy dark:text-white">Provider status & controls</h2>
                <p class="mt-1 text-sm text-brand-muted">Live balance where supported, service/order counts, last request and activation controls.</p>
            </div>
            <a href="{{ route('admin.supplier-logs.index') }}" class="text-sm font-extrabold text-brand-teal hover:text-brand-navy dark:hover:text-white">Supplier logs →</a>
        </div>

        <div class="grid gap-5 lg:grid-cols-2 2xl:grid-cols-3">
            @foreach($providers as $provider)
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-brand-navy dark:text-white">{{ $provider['label'] }}</h3>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-brand-muted">{{ $provider['type'] }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $provider['status'] === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : ($provider['status'] === 'disabled' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-200') }}">
                            {{ $provider['status'] === 'active' ? 'Active' : ($provider['status'] === 'disabled' ? 'Disabled' : 'Not configured') }}
                        </span>
                    </div>

                    <div class="mt-5 rounded-2xl bg-slate-50 p-4 dark:bg-slate-900/60">
                        <p class="text-xs font-bold uppercase tracking-wider text-brand-muted">Account balance</p>
                        <p class="mt-2 text-2xl font-extrabold text-brand-navy dark:text-white">
                            @if($provider['balance'] !== null)
                                {{ number_format($provider['balance'], 2) }} {{ $provider['balance_currency'] }}
                            @elseif($provider['active'])
                                Balance unavailable
                            @else
                                —
                            @endif
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl border border-slate-100 p-3 dark:border-slate-800">
                            <p class="text-xs font-bold uppercase text-brand-muted">Services</p>
                            <p class="mt-1 text-lg font-extrabold text-brand-navy dark:text-white">{{ number_format($provider['services']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 p-3 dark:border-slate-800">
                            <p class="text-xs font-bold uppercase text-brand-muted">Orders</p>
                            <p class="mt-1 text-lg font-extrabold text-brand-navy dark:text-white">{{ number_format($provider['orders']) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-100 p-3 text-sm dark:border-slate-800">
                        <p class="text-xs font-bold uppercase text-brand-muted">Last request</p>
                        @if($provider['last_request'])
                            <p class="mt-1 font-extrabold text-brand-navy dark:text-white">{{ $provider['last_request']['action'] }} · HTTP {{ $provider['last_request']['http_status'] ?? '—' }}</p>
                            <p class="mt-1 text-xs text-brand-muted">{{ $provider['last_request']['ago'] }} · {{ $provider['last_request']['duration_ms'] }}ms</p>
                        @else
                            <p class="mt-1 text-brand-muted">No supplier requests logged yet.</p>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.settings.index') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-extrabold text-brand-navy transition hover:border-brand-teal hover:text-brand-teal dark:border-slate-700 dark:text-white">Configure</a>
                        <a href="{{ route('admin.supplier-logs.index') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-extrabold text-brand-navy transition hover:border-brand-teal hover:text-brand-teal dark:border-slate-700 dark:text-white">Logs</a>
                        <form method="POST" action="{{ route('admin.providers.toggle', $provider['key']) }}" onsubmit="return confirm('{{ $provider['enabled'] ? 'Deactivate' : 'Activate' }} {{ $provider['label'] }}?')">
                            @csrf
                            <button class="rounded-xl px-3 py-2 text-xs font-extrabold {{ $provider['enabled'] ? 'bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-500/15 dark:text-amber-200' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                {{ $provider['enabled'] ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 xl:col-span-2">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                <div>
                    <h2 class="text-xl font-extrabold text-brand-navy dark:text-white">Recent orders</h2>
                    <p class="mt-1 text-sm text-brand-muted">Latest customer activity across SMM and verification services.</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-teal hover:text-brand-navy dark:hover:text-white">
                    View all
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-extrabold uppercase tracking-wider text-brand-muted dark:bg-slate-900/60">
                        <tr>
                            <th class="px-5 py-4">Order</th>
                            <th class="px-5 py-4">Customer</th>
                            <th class="px-5 py-4">Product</th>
                            <th class="px-5 py-4">Total</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentOrders as $order)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/40">
                                <td class="px-5 py-4 font-extrabold">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-teal hover:text-brand-navy dark:hover:text-white">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-5 py-4 text-brand-muted">{{ $order->user?->email ?? 'Guest' }}</td>
                                <td class="max-w-xs truncate px-5 py-4 font-semibold text-brand-text dark:text-slate-200">{{ Str::limit($order->product_name, 42) }}</td>
                                <td class="px-5 py-4 font-extrabold text-brand-navy dark:text-white">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">{{ str_replace('_', ' ', $order->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <p class="text-lg font-extrabold text-brand-navy dark:text-white">No orders yet</p>
                                    <p class="mt-1 text-sm text-brand-muted">New customer orders will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800">
                <h2 class="text-lg font-extrabold text-brand-navy dark:text-white">Needs attention</h2>
                <p class="mt-1 text-sm text-brand-muted">Items that may require admin action.</p>

                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <a href="{{ route('admin.payments.index') }}" class="group flex items-center justify-between gap-4 py-3.5 first:pt-0">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-extrabold text-brand-navy dark:text-white">Pending payments</span>
                                <span class="block text-xs text-brand-muted">Verify or reject deposits</span>
                            </span>
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-brand-navy dark:bg-slate-800 dark:text-white">{{ number_format($stats['pending_payments']) }}</span>
                            <svg class="h-4 w-4 text-brand-muted transition group-hover:translate-x-1 group-hover:text-brand-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a href="{{ route('admin.tickets.index') }}" class="group flex items-center justify-between gap-4 py-3.5">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 10h8M8 14h5"/><path d="M21 12a8 8 0 01-11.6 7.1L4 20l1-4.4A8 8 0 1121 12z"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-extrabold text-brand-navy dark:text-white">Open tickets</span>
                                <span class="block text-xs text-brand-muted">Support conversations</span>
                            </span>
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-brand-navy dark:bg-slate-800 dark:text-white">{{ number_format($stats['open_tickets']) }}</span>
                            <svg class="h-4 w-4 text-brand-muted transition group-hover:translate-x-1 group-hover:text-brand-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a href="{{ route('admin.contact-messages.index') }}" class="group flex items-center justify-between gap-4 py-3.5">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-purple-100 text-purple-700 dark:bg-purple-500/15 dark:text-purple-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-extrabold text-brand-navy dark:text-white">New messages</span>
                                <span class="block text-xs text-brand-muted">Website contact form</span>
                            </span>
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-brand-navy dark:bg-slate-800 dark:text-white">{{ number_format($stats['new_messages']) }}</span>
                            <svg class="h-4 w-4 text-brand-muted transition group-hover:translate-x-1 group-hover:text-brand-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a href="{{ route('admin.supplier-logs.index') }}" class="group flex items-center justify-between gap-4 py-3.5 last:pb-0">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l10 18H2L12 3z"/><path d="M12 10v5m0 3h.01"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-extrabold text-brand-navy dark:text-white">Supplier failures</span>
                                <span class="block text-xs text-brand-muted">Provider API errors</span>
                            </span>
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-brand-navy dark:bg-slate-800 dark:text-white">{{ number_format($stats['supplier_failures']) }}</span>
                            <svg class="h-4 w-4 text-brand-muted transition group-hover:translate-x-1 group-hover:text-brand-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </a>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800">
                <h2 class="text-lg font-extrabold text-brand-navy dark:text-white">Catalogue snapshot</h2>
                <p class="mt-1 text-sm text-brand-muted">Products and customers currently in the system.</p>

                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <a href="{{ route('admin.products.index', ['status' => 'active']) }}" class="group flex items-center justify-between gap-4 py-3 first:pt-0">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8.25l-9-5.25L3 8.25v7.5l9 5.25 9-5.25v-7.5z"/><path d="M3.3 8.8l9 5.2 9-5.2M12 21v-7"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-extrabold text-brand-navy dark:text-white">Active products</span>
                                <span class="block text-xs text-brand-muted">Available in catalogue</span>
                            </span>
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-brand-navy dark:bg-slate-800 dark:text-white">{{ number_format($stats['active_products']) }}</span>
                            <svg class="h-4 w-4 text-brand-muted transition group-hover:translate-x-1 group-hover:text-brand-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a href="{{ route('admin.products.index', ['status' => 'archived']) }}" class="group flex items-center justify-between gap-4 py-3">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18M5 7v12h14V7M8 11h8"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-extrabold text-brand-navy dark:text-white">Archived products</span>
                                <span class="block text-xs text-brand-muted">Hidden from customers</span>
                            </span>
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-brand-navy dark:bg-slate-800 dark:text-white">{{ number_format($stats['archived_products']) }}</span>
                            <svg class="h-4 w-4 text-brand-muted transition group-hover:translate-x-1 group-hover:text-brand-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a href="{{ route('admin.customers.index') }}" class="group flex items-center justify-between gap-4 py-3 last:pb-0">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 11a4 4 0 10-8 0 4 4 0 008 0z"/><path d="M4 20c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-extrabold text-brand-navy dark:text-white">Customers</span>
                                <span class="block text-xs text-brand-muted">Registered customer accounts</span>
                            </span>
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-brand-navy dark:bg-slate-800 dark:text-white">{{ number_format($stats['customers']) }}</span>
                            <svg class="h-4 w-4 text-brand-muted transition group-hover:translate-x-1 group-hover:text-brand-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </a>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800">
                <h2 class="text-lg font-extrabold text-brand-navy dark:text-white">Manage</h2>
                <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                    <a href="{{ route('admin.products.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-sm font-extrabold text-brand-navy transition hover:border-brand-teal hover:text-brand-teal dark:border-slate-800 dark:text-white">Products <span>→</span></a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-sm font-extrabold text-brand-navy transition hover:border-brand-teal hover:text-brand-teal dark:border-slate-800 dark:text-white">Categories <span>→</span></a>
                    <a href="{{ route('admin.countries.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-sm font-extrabold text-brand-navy transition hover:border-brand-teal hover:text-brand-teal dark:border-slate-800 dark:text-white">Countries <span>→</span></a>
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-sm font-extrabold text-brand-navy transition hover:border-brand-teal hover:text-brand-teal dark:border-slate-800 dark:text-white">Settings <span>→</span></a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
