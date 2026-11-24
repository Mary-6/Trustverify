@extends('layouts.account')

@section('title', 'Contact Messages | Admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-brand-navy">Contact messages</h1>
        <p class="mt-1 text-brand-muted">Website contact form submissions from customers and visitors.</p>
    </div>

    <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_180px_auto]">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email, subject or message" class="rounded-xl border-slate-300 px-4 py-3 text-sm">
        <select name="status" class="rounded-xl border-slate-300 px-4 py-3 text-sm">
            <option value="">All statuses</option>
            @foreach(['new','read','replied'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
        </select>
        <button class="rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-brand-bg text-left text-xs uppercase text-brand-muted">
                <tr><th class="px-5 py-3">Message</th><th class="px-5 py-3">From</th><th class="px-5 py-3">Phone</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Received</th><th class="px-5 py-3 text-right">Open</th></tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr class="border-t border-slate-100">
                        <td class="px-5 py-4"><p class="font-bold text-brand-navy">{{ $message->subject }}</p><p class="text-xs text-brand-muted">#{{ $message->id }}</p></td>
                        <td class="px-5 py-4"><p class="font-semibold">{{ $message->name }}</p><p class="text-xs text-brand-muted">{{ $message->email }}</p></td>
                        <td class="px-5 py-4 text-brand-muted">{{ $message->phone ?: '—' }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-bold {{ $message->status === 'new' ? 'bg-brand-gold/20 text-brand-navy' : 'bg-brand-teal-soft text-brand-navy' }}">{{ ucfirst($message->status) }}</span></td>
                        <td class="px-5 py-4 text-brand-muted">{{ $message->created_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('admin.contact-messages.show', $message) }}" class="font-bold text-brand-teal hover:underline">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-brand-muted">No contact messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $messages->links() }}
</div>
@endsection
