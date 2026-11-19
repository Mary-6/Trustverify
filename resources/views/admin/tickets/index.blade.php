@extends('layouts.account')

@section('title', 'Support Tickets | Admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-brand-navy">Support tickets</h1>
        <p class="mt-1 text-brand-muted">Customer conversations, priorities and assignment.</p>
    </div>

    <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_160px_160px_auto]">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search subject or customer" class="rounded-xl border-slate-300 px-4 py-3 text-sm">
        <select name="status" class="rounded-xl border-slate-300 px-4 py-3 text-sm">
            <option value="">All statuses</option>
            @foreach(['open','pending','closed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach
        </select>
        <select name="priority" class="rounded-xl border-slate-300 px-4 py-3 text-sm">
            <option value="">All priorities</option>
            @foreach(['low','normal','high','urgent'] as $priority)<option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ $priority }}</option>@endforeach
        </select>
        <button class="rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-brand-bg text-left text-xs uppercase text-brand-muted">
                <tr><th class="px-5 py-3">Ticket</th><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Priority</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Assigned</th><th class="px-5 py-3">Last reply</th><th class="px-5 py-3 text-right">Open</th></tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr class="border-t border-slate-100">
                        <td class="px-5 py-4"><p class="font-bold text-brand-navy">{{ $ticket->subject }}</p><p class="text-xs text-brand-muted">#{{ $ticket->id }}</p></td>
                        <td class="px-5 py-4"><p class="font-semibold">{{ $ticket->user?->name }}</p><p class="text-xs text-brand-muted">{{ $ticket->user?->email }}</p></td>
                        <td class="px-5 py-4">{{ $ticket->priority }}</td>
                        <td class="px-5 py-4"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $ticket->status }}</span></td>
                        <td class="px-5 py-4">{{ $ticket->assignee?->name ?: 'Unassigned' }}</td>
                        <td class="px-5 py-4 text-brand-muted">{{ $ticket->last_reply_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('admin.tickets.show', $ticket) }}" class="font-bold text-brand-teal hover:underline">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-brand-muted">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $tickets->links() }}
</div>
@endsection
