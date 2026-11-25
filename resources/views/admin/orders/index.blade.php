@extends('layouts.admin')

@section('title', 'Admin Orders | TrustVerifySMS')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-brand-navy">Orders</h1>
            <p class="mt-1 text-brand-muted">Review, retry, cancel or reconcile marketplace orders.</p>
        </div>
        <form method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search order/reference" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
            <select name="status" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach(['pending','processing','submitted','completed','failed','cancelled','refunded'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Order</th><th class="pb-3">Customer</th><th class="pb-3">Product</th><th class="pb-3">Total</th><th class="pb-3">Supplier ref</th><th class="pb-3">Status</th><th class="pb-3"></th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="border-t border-slate-100">
                            <td class="py-3 font-semibold"><a class="text-brand-teal" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td class="py-3">{{ $order->user?->email }}</td>
                            <td class="py-3">{{ Str::limit($order->product_name, 35) }}</td>
                            <td class="py-3 font-semibold">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</td>
                            <td class="py-3 text-brand-muted">{{ $order->supplier_reference ?: '—' }}</td>
                            <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $order->status }}</span></td>
                            <td class="py-3"><a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-bold text-brand-teal">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-brand-muted">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
