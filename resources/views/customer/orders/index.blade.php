@extends('layouts.account')

@section('title', 'My Orders | TrustVerifySMS')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'submitted' => 'Submitted',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">My Orders</p>
            <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">Track every order.</h1>
            <p class="mt-1 text-brand-muted">Search by order ID, product or submitted link and filter by status.</p>
        </div>
        <a href="{{ route('services.index') }}" class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">New order</a>
    </div>

    <form method="GET" action="{{ route('dashboard.orders.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row">
            <input name="q" value="{{ $query }}" placeholder="Search order ID, product or link" class="flex-1 rounded-xl border-slate-300 px-4 py-3">
            <select name="status" class="rounded-xl border-slate-300 px-4 py-3">
                <option value="">All statuses</option>
                @foreach($statuses as $value)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $statusLabels[$value] ?? str_replace('_', ' ', $value) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-brand-navy px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-navy-light">Search</button>
        </div>
    </form>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('dashboard.orders.index', array_filter(['q' => $query])) }}" class="rounded-full px-4 py-2 text-sm font-bold {{ $status === '' ? 'bg-brand-navy text-white' : 'bg-white text-brand-navy border border-slate-200' }}">All</a>
        @foreach($statuses as $value)
            <a href="{{ route('dashboard.orders.index', array_filter(['q' => $query, 'status' => $value])) }}" class="rounded-full px-4 py-2 text-sm font-bold {{ $status === $value ? 'bg-brand-navy text-white' : 'bg-white text-brand-navy border border-slate-200' }}">{{ $statusLabels[$value] ?? str_replace('_', ' ', $value) }}</a>
        @endforeach
    </div>

    @if($orders->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <p class="font-extrabold text-brand-navy">No orders found.</p>
            <p class="mt-2 text-sm text-brand-muted">Try a different search or choose a service to place your first order.</p>
            <a href="{{ route('services.index') }}" class="mt-5 inline-flex rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Browse services</a>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($orders as $order)
                @php($orderLink = $order->payload['link'] ?? null)
                <a href="{{ route('dashboard.orders.show', $order) }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-extrabold text-brand-navy">{{ $order->order_number }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-brand-teal">{{ $order->product?->category?->name ?? str_replace('_', ' ', $order->type) }}</p>
                        </div>
                        <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-xs font-extrabold text-brand-navy">{{ $statusLabels[$order->status] ?? str_replace('_', ' ', $order->status) }}</span>
                    </div>
                    <h3 class="mt-3 line-clamp-2 font-extrabold text-brand-navy">{{ $order->product_name }}</h3>
                    @if($orderLink)
                        <p class="mt-2 truncate text-sm text-brand-muted">{{ $orderLink }}</p>
                    @endif
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div><p class="text-xs font-bold uppercase text-brand-muted">Quantity</p><p class="font-extrabold text-brand-navy">{{ number_format((float) $order->quantity) }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-brand-muted">Total</p><p class="font-extrabold text-brand-navy">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</p></div>
                    </div>
                    @if($order->supplier_status)
                        <p class="mt-3 text-xs font-semibold text-brand-muted">Supplier: {{ str_replace('_', ' ', $order->supplier_status) }}</p>
                    @endif
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-brand-muted">
                        <span>{{ $order->created_at->format('M j, Y H:i') }}</span>
                        <span class="font-extrabold text-brand-teal">View details →</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div>{{ $orders->links() }}</div>
    @endif
</div>
@endsection
