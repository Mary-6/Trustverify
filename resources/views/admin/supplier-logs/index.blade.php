@extends('layouts.admin')

@section('title', 'Supplier Logs | Admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-brand-navy">Supplier logs</h1>
        <p class="mt-1 text-brand-muted">Audit supplier requests and responses for reconciliation.</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="mb-5 flex gap-2">
            <input type="text" name="supplier" value="{{ request('supplier') }}" placeholder="Supplier" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
            <select name="status" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach(['success','failed','pending'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Time</th><th class="pb-3">Order</th><th class="pb-3">Supplier</th><th class="pb-3">Action</th><th class="pb-3">Endpoint</th><th class="pb-3">HTTP</th><th class="pb-3">Status</th><th class="pb-3">Duration</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-t border-slate-100">
                            <td class="py-3 text-brand-muted">{{ $log->created_at->format('M j H:i') }}</td>
                            <td class="py-3 font-semibold">{{ $log->order?->order_number ?: '—' }}</td>
                            <td class="py-3">{{ $log->supplier }}</td>
                            <td class="py-3">{{ $log->action }}</td>
                            <td class="py-3 text-brand-muted">{{ Str::limit($log->endpoint, 40) }}</td>
                            <td class="py-3">{{ $log->http_status ?: '—' }}</td>
                            <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $log->status }}</span></td>
                            <td class="py-3 text-brand-muted">{{ $log->duration_ms }}ms</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-brand-muted">No supplier logs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
