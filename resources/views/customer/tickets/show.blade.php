@extends('layouts.account')

@section('title', $ticket->subject.' | Support')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Support ticket</p>
                <h1 class="mt-2 text-2xl font-extrabold text-brand-navy">{{ $ticket->subject }}</h1>
                <p class="mt-1 text-sm text-brand-muted">Priority: {{ $ticket->priority }} • Status: {{ $ticket->status }}</p>
            </div>
            <a href="{{ route('dashboard.tickets.index') }}" class="text-sm font-bold text-brand-teal hover:underline">Back to tickets</a>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($ticket->messages as $message)
            <div class="rounded-2xl border {{ $message->is_admin ? 'border-brand-teal/30 bg-brand-teal-soft/60' : 'border-slate-200 bg-white' }} p-5">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-extrabold {{ $message->is_admin ? 'text-brand-navy' : 'text-brand-text' }}">{{ $message->is_admin ? 'TrustVerifySMS Support' : ($message->user?->name ?: 'Customer') }}</p>
                    <p class="text-xs text-brand-muted">{{ $message->created_at->format('M j, Y H:i') }}</p>
                </div>
                <div class="mt-3 whitespace-pre-line text-sm leading-6 text-brand-text">{{ $message->body }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="font-extrabold text-brand-navy">Reply</h2>
        <form method="POST" action="{{ route('dashboard.tickets.messages.store', $ticket) }}" class="mt-4 space-y-4">
            @csrf
            <textarea name="body" rows="5" required class="w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Write your reply">{{ old('body') }}</textarea>
            @error('body')<p class="text-sm text-brand-error">{{ $message }}</p>@enderror
            <button class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Send reply</button>
        </form>
    </div>
</div>
@endsection
