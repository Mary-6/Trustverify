@extends('layouts.account')

@section('title', $ticket->subject.' | Admin Ticket')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Admin ticket #{{ $ticket->id }}</p>
            <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">{{ $ticket->subject }}</h1>
            <p class="mt-1 text-brand-muted">Customer: {{ $ticket->user?->name }} ({{ $ticket->user?->email }})</p>
        </div>
        <a href="{{ route('admin.tickets.index') }}" class="text-sm font-bold text-brand-teal hover:underline">Back to tickets</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[380px_1fr]">
        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Ticket controls</h2>
                <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}" class="mt-4 space-y-4">
                    @csrf @method('PATCH')
                    <div>
                        <label class="text-sm font-bold text-brand-text">Status</label>
                        <select name="status" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                            @foreach(['open','pending','closed'] as $status)<option value="{{ $status }}" @selected($ticket->status === $status)>{{ $status }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-brand-text">Priority</label>
                        <select name="priority" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                            @foreach(['low','normal','high','urgent'] as $priority)<option value="{{ $priority }}" @selected($ticket->priority === $priority)>{{ $priority }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-brand-text">Assigned admin</label>
                        <select name="assigned_to" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                            <option value="">Unassigned</option>
                            @foreach($admins as $admin)<option value="{{ $admin->id }}" @selected($ticket->assigned_to === $admin->id)>{{ $admin->name }} — {{ $admin->email }}</option>@endforeach
                        </select>
                    </div>
                    <button class="w-full rounded-xl bg-brand-navy px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-navy-light">Save ticket</button>
                </form>
            </div>
        </aside>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Conversation</h2>
                <div class="mt-4 space-y-4">
                    @foreach($ticket->messages as $message)
                        <div class="rounded-2xl border {{ $message->is_admin ? 'border-brand-teal/30 bg-brand-teal-soft/60' : 'border-slate-200 bg-brand-bg' }} p-5">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm font-extrabold text-brand-navy">{{ $message->is_admin ? ($message->user?->name ?: 'Admin') : ($message->user?->name ?: 'Customer') }}</p>
                                <p class="text-xs text-brand-muted">{{ $message->created_at->format('M j, Y H:i') }}</p>
                            </div>
                            <div class="mt-3 whitespace-pre-line text-sm leading-6 text-brand-text">{{ $message->body }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Reply to customer</h2>
                <form method="POST" action="{{ route('admin.tickets.messages.store', $ticket) }}" class="mt-4 space-y-4">
                    @csrf
                    <textarea name="body" rows="5" required class="w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Write a clear support reply">{{ old('body') }}</textarea>
                    @error('body')<p class="text-sm text-brand-error">{{ $message }}</p>@enderror
                    <button class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Send reply</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
