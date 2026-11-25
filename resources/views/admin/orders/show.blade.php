@extends('layouts.admin')

@section('title', 'Order '.$order->order_number.' | Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Admin order</p>
            <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">{{ $order->order_number }}</h1>
            <p class="mt-1 text-brand-muted">Customer: {{ $order->user?->email }}</p>
        </div>
        <span class="rounded-full bg-brand-teal-soft px-4 py-2 text-sm font-extrabold text-brand-navy">{{ $order->status }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Order summary</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Product</dt><dd class="mt-1 font-semibold">{{ $order->product_name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Type</dt><dd class="mt-1 font-semibold">{{ str_replace('_', ' ', $order->type) }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Quantity</dt><dd class="mt-1 font-semibold">{{ $order->quantity }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Total</dt><dd class="mt-1 font-semibold">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Supplier cost</dt><dd class="mt-1 font-semibold">{{ $order->supplier_cost ? number_format((float) $order->supplier_cost, 0) : '—' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Idempotency key</dt><dd class="mt-1 break-all font-mono text-xs">{{ $order->idempotency_key }}</dd></div>
                </dl>
                @if($order->payload)
                    <h3 class="mt-6 font-bold text-brand-navy">Payload</h3>
                    <pre class="mt-2 overflow-x-auto rounded-xl bg-brand-bg p-4 text-xs">{{ json_encode($order->payload, JSON_PRETTY_PRINT) }}</pre>
                @endif
                @if($order->delivery_data)
                    <h3 class="mt-6 font-bold text-brand-navy">Delivery data</h3>
                    <pre class="mt-2 overflow-x-auto rounded-xl bg-brand-bg p-4 text-xs">{{ json_encode($order->delivery_data, JSON_PRETTY_PRINT) }}</pre>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Status history</h2>
                <ol class="mt-4 space-y-4">
                    @foreach($order->histories as $history)
                        <li class="flex gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-brand-teal"></span><div><p class="text-sm font-bold">{{ str_replace('_', ' ', $history->status) }}</p><p class="text-sm text-brand-muted">{{ $history->note }}</p><p class="text-xs text-brand-muted">{{ $history->created_at->diffForHumans() }}</p></div></li>
                    @endforeach
                </ol>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Update order</h2>
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 space-y-4">
                    @csrf @method('PATCH')
                    <div>
                        <label class="text-sm font-bold text-brand-text" for="status">Status</label>
                        <select id="status" name="status" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                            @foreach(['pending','processing','submitted','completed','failed','cancelled','refunded'] as $status)
                                <option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="text-sm font-bold text-brand-text" for="supplier_reference">Supplier reference</label><input id="supplier_reference" name="supplier_reference" value="{{ old('supplier_reference', $order->supplier_reference) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
                    <div><label class="text-sm font-bold text-brand-text" for="supplier_status">Supplier status</label><input id="supplier_status" name="supplier_status" value="{{ old('supplier_status', $order->supplier_status) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
                    <div><label class="text-sm font-bold text-brand-text" for="admin_notes">Admin notes</label><textarea id="admin_notes" name="admin_notes" rows="4" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">{{ old('admin_notes', $order->admin_notes) }}</textarea></div>
                    <div><label class="text-sm font-bold text-brand-text" for="status_note">Status note</label><input id="status_note" name="status_note" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Optional note"></div>
                    <button class="w-full rounded-xl bg-brand-navy px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-navy-light">Save changes</button>
                </form>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Supplier actions</h2>
                <div class="mt-4 grid gap-3">
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}">@csrf @method('PATCH')<input type="hidden" name="action" value="resubmit"><button class="w-full rounded-xl bg-brand-teal px-4 py-2 text-sm font-extrabold text-brand-navy">Resubmit to supplier</button></form>
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}">@csrf @method('PATCH')<input type="hidden" name="action" value="sync_status"><button class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-brand-navy">Sync supplier status</button></form>
                    @if(! in_array($order->status, ['completed', 'refunded']))
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" onsubmit="return confirm('Refund this order amount to the customer wallet?')">@csrf @method('PATCH')<input type="hidden" name="action" value="refund"><button class="w-full rounded-xl bg-brand-error px-4 py-2 text-sm font-extrabold text-white">Refund wallet</button></form>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
