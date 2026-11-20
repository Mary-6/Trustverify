@extends('layouts.account')

@section('title', 'Support Tickets | TrustVerifySMS')

@section('content')
<div class="grid gap-6 lg:grid-cols-[380px_1fr]">
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h1 class="text-2xl font-extrabold text-brand-navy">New ticket</h1>
        <form method="POST" action="{{ route('dashboard.tickets.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-bold text-brand-text" for="subject">Subject</label>
                <input id="subject" name="subject" value="{{ old('subject') }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                @error('subject')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-bold text-brand-text" for="priority">Priority</label>
                <select id="priority" name="priority" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                    <option value="low">Low</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-bold text-brand-text" for="body">Message</label>
                <textarea id="body" name="body" rows="6" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Include order number, payment reference or number details where relevant.">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
            </div>
            <button class="w-full rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Create ticket</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-xl font-extrabold text-brand-navy">Your tickets</h2>
        @if($tickets->isEmpty())
            <p class="mt-3 text-sm text-brand-muted">No support tickets yet.</p>
        @else
            <div class="mt-4 space-y-3">
                @foreach($tickets as $ticket)
                    <a href="{{ route('dashboard.tickets.show', $ticket) }}" class="block rounded-2xl border border-slate-200 p-4 transition hover:border-brand-teal/50 hover:bg-brand-teal-soft/40">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="font-bold text-brand-navy">{{ $ticket->subject }}</p>
                            <span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $ticket->status }}</span>
                        </div>
                        <p class="mt-1 text-sm text-brand-muted">Priority: {{ $ticket->priority }} • Last reply {{ $ticket->last_reply_at?->diffForHumans() ?? 'never' }}</p>
                    </a>
                @endforeach
            </div>
            <div class="mt-5">{{ $tickets->links() }}</div>
        @endif
    </div>
</div>
@endsection
