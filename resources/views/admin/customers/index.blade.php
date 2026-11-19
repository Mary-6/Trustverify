@extends('layouts.account')

@section('title', 'Admin Customers | Trustverifysms')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-brand-navy">Customers</h1>
        <p class="mt-1 text-brand-muted">Customer accounts, wallet balances and activity.</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="mb-5 flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or email" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
            <button class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Search</button>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Customer</th><th class="pb-3">Phone</th><th class="pb-3">Wallet</th><th class="pb-3">Orders</th><th class="pb-3">Status</th><th class="pb-3">Joined</th></tr></thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr class="border-t border-slate-100">
                            <td class="py-3"><p class="font-semibold">{{ $customer->name }}</p><p class="text-brand-muted">{{ $customer->email }}</p></td>
                            <td class="py-3">{{ $customer->phone ?: '—' }}</td>
                            <td class="py-3 font-semibold">{{ number_format((float) ($customer->wallet?->balance ?? 0), 0) }} {{ $customer->wallet?->currency ?? 'XAF' }}</td>
                            <td class="py-3">{{ $customer->orders_count ?? $customer->orders()->count() }}</td>
                            <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $customer->status }}</span></td>
                            <td class="py-3 text-brand-muted">{{ $customer->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-brand-muted">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $customers->links() }}</div>
    </div>
</div>
@endsection
