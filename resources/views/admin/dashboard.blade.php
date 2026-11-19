@extends('layouts.account')

@section('title', 'Admin Dashboard | Trustverifysms')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl bg-brand-navy p-8 text-white">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Administration</p>
        <h1 class="mt-2 text-3xl font-extrabold">Operations overview</h1>
        <p class="mt-2 text-slate-300">Monitor orders, payments, products and supplier activity.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Revenue</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format((float) $stats['revenue'], 0) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Orders</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format($stats['orders']) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Pending orders</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format($stats['pending_orders']) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Active products</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format($stats['active_products']) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Archived products</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format($stats['archived_products']) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Customers</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format($stats['customers']) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Pending payments</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format($stats['pending_payments']) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Open tickets</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format($stats['open_tickets']) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-brand-muted">Supplier failures</p><p class="mt-2 text-2xl font-extrabold text-brand-navy">{{ number_format($stats['supplier_failures']) }}</p></div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.products.create') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-brand-navy shadow-sm hover:border-brand-teal">Add product</a>
        <a href="{{ route('admin.products.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-brand-navy shadow-sm hover:border-brand-teal">Manage products</a>
        <a href="{{ route('admin.payments.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-brand-navy shadow-sm hover:border-brand-teal">Review payments</a>
        <a href="{{ route('admin.tickets.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-brand-navy shadow-sm hover:border-brand-teal">Open tickets</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-extrabold text-brand-navy">Recent orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-bold text-brand-teal">View all</a>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Order</th><th class="pb-3">Customer</th><th class="pb-3">Product</th><th class="pb-3">Total</th><th class="pb-3">Status</th></tr></thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr class="border-t border-slate-100">
                            <td class="py-3 font-semibold"><a class="text-brand-teal" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td class="py-3">{{ $order->user?->email }}</td>
                            <td class="py-3">{{ Str::limit($order->product_name, 35) }}</td>
                            <td class="py-3 font-semibold">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</td>
                            <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $order->status }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
