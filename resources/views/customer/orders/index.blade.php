@extends('layouts.account')

@section('title', 'Orders | TrustVerifySMS')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-brand-navy">Orders</h1>
            <p class="mt-1 text-brand-muted">Track every purchase, status change and supplier reference.</p>
        </div>
        <a href="{{ route('services.index') }}" class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">New order</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        @if($orders->isEmpty())
            <p class="text-brand-muted">No orders found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Order</th><th class="pb-3">Product</th><th class="pb-3">Qty</th><th class="pb-3">Total</th><th class="pb-3">Status</th><th class="pb-3">Date</th></tr></thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr class="border-t border-slate-100 hover:bg-brand-bg/60">
                                <td class="py-3 font-semibold"><a class="text-brand-teal" href="{{ route('dashboard.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                <td class="py-3">{{ Str::limit($order->product_name, 45) }}</td>
                                <td class="py-3">{{ $order->quantity }}</td>
                                <td class="py-3 font-semibold">{{ number_format((float) $order->total_price, 0) }} {{ $order->currency }}</td>
                                <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $order->status }}</span></td>
                                <td class="py-3 text-brand-muted">{{ $order->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
