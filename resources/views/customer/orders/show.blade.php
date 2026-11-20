@extends('layouts.account')

@section('title', 'Order '.$order->order_number.' | TrustVerifySMS')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Order details</p>
            <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">{{ $order->order_number }}</h1>
            <p class="mt-1 text-brand-muted">Placed {{ $order->created_at->diffForHumans() }}</p>
        </div>
        <span class="rounded-full bg-brand-teal-soft px-4 py-2 text-sm font-extrabold text-brand-navy">{{ $order->status }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Summary</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Product</dt><dd class="mt-1 font-semibold">{{ $order->product_name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Type</dt><dd class="mt-1 font-semibold">{{ str_replace('_', ' ', $order->type) }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Quantity</dt><dd class="mt-1 font-semibold">{{ $order->quantity }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Total</dt><dd class="mt-1 font-semibold">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Supplier reference</dt><dd class="mt-1 font-semibold">{{ $order->supplier_reference ?: 'Pending' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Supplier status</dt><dd class="mt-1 font-semibold">{{ $order->supplier_status ?: 'Not reported' }}</dd></div>
                </dl>
            </div>

            @if($order->delivery_data)
                <div class="rounded-2xl border border-brand-teal/30 bg-brand-teal-soft p-6">
                    <h2 class="font-extrabold text-brand-navy">Delivery data</h2>
                    <pre class="mt-3 overflow-x-auto rounded-xl bg-white p-4 text-xs text-brand-text">{{ json_encode($order->delivery_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Status history</h2>
                <ol class="mt-4 space-y-4">
                    @forelse($order->histories as $history)
                        <li class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-brand-teal"></span>
                            <div>
                                <p class="text-sm font-bold text-brand-navy">{{ str_replace('_', ' ', $history->status) }}</p>
                                <p class="text-sm text-brand-muted">{{ $history->note }}</p>
                                <p class="text-xs text-brand-muted">{{ $history->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-brand-muted">No history recorded.</li>
                    @endforelse
                </ol>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Need help?</h2>
                <p class="mt-2 text-sm text-brand-muted">If this order is delayed or failed, open a ticket and include {{ $order->order_number }}.</p>
                <a href="{{ route('dashboard.tickets.index') }}" class="mt-4 inline-flex rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Support</a>
            </div>
            @if($order->product)
                <a href="{{ route('services.show', $order->product) }}" class="block rounded-2xl border border-slate-200 bg-white p-6 text-sm font-bold text-brand-teal hover:bg-brand-bg">View product again →</a>
            @endif
        </aside>
    </div>
</div>
@endsection
