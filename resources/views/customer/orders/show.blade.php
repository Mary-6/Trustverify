@extends('layouts.account')

@section('title', 'Order '.$order->order_number.' | TrustVerifySMS')

@section('content')
@php
    $delivery = $order->delivery_data ?? [];
    $progress = $progress ?? null;
    $canRequestRefill = $canRequestRefill ?? false;
    $canRequestCancel = $canRequestCancel ?? false;
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
    <a href="{{ route('dashboard.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-teal hover:text-brand-gold">
        <span aria-hidden="true">←</span> My Orders
    </a>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Order details</p>
            <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">{{ $order->order_number }}</h1>
            <p class="mt-1 text-brand-muted">Placed {{ $order->created_at->diffForHumans() }}</p>
        </div>
        <span class="rounded-full bg-brand-teal-soft px-4 py-2 text-sm font-extrabold text-brand-navy">{{ $statusLabels[$order->status] ?? str_replace('_', ' ', $order->status) }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Summary</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Product</dt><dd class="mt-1 font-semibold">{{ $order->product_name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Type</dt><dd class="mt-1 font-semibold">{{ str_replace('_', ' ', $order->type) }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Quantity</dt><dd class="mt-1 font-semibold">{{ number_format((float) $order->quantity) }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Total</dt><dd class="mt-1 font-semibold">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Supplier reference</dt><dd class="mt-1 font-semibold">{{ $order->supplier_reference ?: 'Pending' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Supplier status</dt><dd class="mt-1 font-semibold">{{ $order->supplier_status ?: 'Not reported' }}</dd></div>
                </dl>
            </div>

            @if($order->type === 'smm_service' && $progress)
                <div class="rounded-2xl border border-brand-teal/30 bg-brand-teal-soft p-6">
                    <h2 class="font-extrabold text-brand-navy">Delivery progress</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div><p class="text-xs font-bold uppercase text-brand-muted">Start count</p><p class="mt-1 text-lg font-extrabold text-brand-navy">{{ isset($progress['start_count']) ? number_format((float) $progress['start_count']) : '—' }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-brand-muted">Remaining</p><p class="mt-1 text-lg font-extrabold text-brand-navy">{{ isset($progress['remains']) ? number_format((float) $progress['remains']) : '—' }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-brand-muted">Delivered</p><p class="mt-1 text-lg font-extrabold text-brand-navy">{{ isset($progress['delivered']) ? number_format((float) $progress['delivered']) : '—' }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-brand-muted">Supplier charge</p><p class="mt-1 text-lg font-extrabold text-brand-navy">{{ isset($progress['charge']) ? number_format((float) $progress['charge'], 2).' '.($progress['currency'] ?? '') : '—' }}</p></div>
                    </div>
                    @if(isset($progress['percent']))
                        <div class="mt-5">
                            <div class="flex items-center justify-between text-xs font-bold text-brand-muted">
                                <span>Progress</span>
                                <span>{{ rtrim(rtrim(number_format((float) $progress['percent'], 1), '0'), '.') }}%</span>
                            </div>
                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-white">
                                <div class="h-full rounded-full bg-brand-teal" style="width: {{ min(100, (float) $progress['percent']) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @elseif($order->type === 'virtual_number' && ! empty($delivery['sms_code']))
                <div class="rounded-2xl border border-brand-teal/30 bg-brand-teal-soft p-6">
                    <h2 class="font-extrabold text-brand-navy">Verification code</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div><p class="text-xs font-bold uppercase text-brand-muted">Number</p><p class="mt-1 text-lg font-extrabold text-brand-navy">{{ $delivery['number'] ?? '—' }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-brand-muted">Code</p><p class="mt-1 text-lg font-extrabold text-brand-teal">{{ $delivery['sms_code'] }}</p></div>
                    </div>
                    @if(! empty($delivery['sms_content']))
                        <p class="mt-4 rounded-xl bg-white p-4 text-sm text-brand-muted">{{ $delivery['sms_content'] }}</p>
                    @endif
                </div>
            @elseif($order->delivery_data)
                <div class="rounded-2xl border border-brand-teal/30 bg-brand-teal-soft p-6">
                    <h2 class="font-extrabold text-brand-navy">Delivery data</h2>
                    <pre class="mt-3 overflow-x-auto rounded-xl bg-white p-4 text-xs text-brand-text">{{ json_encode($order->delivery_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif

            @if(($delivery['refill_id'] ?? null) || ($delivery['refill_status'] ?? null))
                <div class="rounded-2xl border border-brand-gold/30 bg-brand-gold/10 p-5 text-sm">
                    <p class="font-extrabold text-brand-navy">Refill</p>
                    <p class="mt-1 text-brand-muted">{{ $delivery['refill_status'] ?? 'requested' }}@if($delivery['refill_id'] ?? null) · ID {{ $delivery['refill_id'] }}@endif</p>
                </div>
            @endif
            @if(($delivery['cancel_status'] ?? null) || ($delivery['cancel_result'] ?? null))
                <div class="rounded-2xl border border-brand-error/30 bg-brand-error/10 p-5 text-sm">
                    <p class="font-extrabold text-brand-navy">Cancellation</p>
                    <p class="mt-1 text-brand-muted">{{ $delivery['cancel_status'] ?? 'requested' }}</p>
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
            @if($canRequestCancel || $canRequestRefill || $order->product)
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-extrabold text-brand-navy">Order actions</h2>
                    <div class="mt-4 space-y-3">
                        @if($canRequestCancel)
                            <form method="POST" action="{{ route('dashboard.orders.cancel', $order) }}" onsubmit="return confirm('Request cancellation for {{ $order->order_number }}?');">
                                @csrf
                                <button class="w-full rounded-xl bg-brand-navy px-4 py-3 text-sm font-extrabold text-white hover:bg-brand-navy-light">Request cancellation</button>
                            </form>
                        @endif
                        @if($canRequestRefill)
                            <form method="POST" action="{{ route('dashboard.orders.refill', $order) }}" onsubmit="return confirm('Request a refill for {{ $order->order_number }}?');">
                                @csrf
                                <button class="w-full rounded-xl bg-brand-teal px-4 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Request refill</button>
                            </form>
                        @endif
                        @if($order->product)
                            <a href="{{ route('services.show', $order->product) }}" class="block rounded-xl border border-brand-teal/40 px-4 py-3 text-center text-sm font-extrabold text-brand-teal hover:bg-brand-teal-soft">Order again</a>
                        @endif
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Need help?</h2>
                <p class="mt-2 text-sm text-brand-muted">If this order is delayed or failed, open a ticket and include {{ $order->order_number }}.</p>
                <a href="{{ route('dashboard.tickets.index') }}" class="mt-4 inline-flex rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Support</a>
            </div>
        </aside>
    </div>
</div>
@endsection
