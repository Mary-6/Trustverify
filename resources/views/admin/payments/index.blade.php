@extends('layouts.account')

@section('title', 'Admin Payments | Trustverifysms')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-brand-navy">Payments</h1>
        <p class="mt-1 text-brand-muted">Verify deposits before crediting wallets.</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="mb-5 flex gap-2">
            <select name="status" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach(['pending','success','failed','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Reference</th><th class="pb-3">Customer</th><th class="pb-3">Amount</th><th class="pb-3">Method</th><th class="pb-3">Status</th><th class="pb-3">Date</th><th class="pb-3"></th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="border-t border-slate-100">
                            <td class="py-3 font-semibold">{{ $payment->reference }}</td>
                            <td class="py-3">{{ $payment->user?->email }}</td>
                            <td class="py-3 font-semibold">{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency }}</td>
                            <td class="py-3">{{ str_replace('_', ' ', $payment->gateway) }}</td>
                            <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $payment->status }}</span></td>
                            <td class="py-3 text-brand-muted">{{ $payment->created_at->format('M j, Y H:i') }}</td>
                            <td class="py-3">
                                @if($payment->status === 'pending')
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">@csrf<button class="text-sm font-bold text-brand-success">Verify</button></form>
                                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">@csrf<button class="text-sm font-bold text-brand-error">Reject</button></form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-brand-muted">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $payments->links() }}</div>
    </div>
</div>
@endsection
