@extends('layouts.admin')

@section('title', 'Contact Message | Admin')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-brand-navy">{{ $message->subject }}</h1>
            <p class="mt-1 text-brand-muted">Message #{{ $message->id }} · {{ $message->created_at?->format('M j, Y g:i A') }}</p>
        </div>
        <a href="{{ route('admin.contact-messages.index') }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← All messages</a>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr_340px]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">Message</h2>
            <p class="mt-4 whitespace-pre-line rounded-2xl bg-brand-bg p-5 text-sm leading-7 text-brand-text">{{ $message->message }}</p>
        </div>

        <aside class="space-y-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-extrabold text-brand-navy">Sender</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="font-bold text-brand-muted">Name</dt><dd class="mt-1 font-semibold text-brand-navy">{{ $message->name }}</dd></div>
                    <div><dt class="font-bold text-brand-muted">Email</dt><dd class="mt-1 break-all font-semibold text-brand-navy"><a class="text-brand-teal hover:underline" href="mailto:{{ $message->email }}">{{ $message->email }}</a></dd></div>
                    <div><dt class="font-bold text-brand-muted">Phone</dt><dd class="mt-1 font-semibold text-brand-navy">{{ $message->phone ?: '—' }}</dd></div>
                    @if($message->user)<div><dt class="font-bold text-brand-muted">Account</dt><dd class="mt-1 font-semibold text-brand-navy">{{ $message->user->name }} · {{ $message->user->email }}</dd></div>@endif
                </dl>
            </div>

            <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}" class="rounded-2xl border border-slate-200 bg-white p-5">
                @csrf @method('PATCH')
                <label class="text-sm font-bold text-brand-text" for="status">Status</label>
                <select id="status" name="status" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-sm">
                    @foreach(['new','read','replied'] as $status)<option value="{{ $status }}" @selected($message->status === $status)>{{ ucfirst($status) }}</option>@endforeach
                </select>
                <button class="mt-4 w-full rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Save status</button>
            </form>
        </aside>
    </div>
</div>
@endsection
