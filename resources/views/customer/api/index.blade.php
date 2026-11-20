@extends('layouts.account')

@section('title', 'API Access | TrustVerifySMS')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-brand-navy">API access</h1>
            <p class="mt-1 text-brand-muted">Generate scoped keys for implemented customer endpoints only.</p>
        </div>
        <a href="{{ route('api.docs') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-brand-navy hover:bg-slate-50">API docs</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[380px_1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">Create key</h2>
            <form method="POST" action="{{ route('dashboard.api-keys.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-bold text-brand-text" for="name">Key name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                    @error('name')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-bold text-brand-text" for="expires_at">Expires at</label>
                    <input id="expires_at" name="expires_at" type="datetime-local" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                </div>
                <button class="w-full rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Generate key</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">Your keys</h2>
            @if($keys->isEmpty())
                <p class="mt-3 text-sm text-brand-muted">No API keys yet.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach($keys as $key)
                        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-brand-navy">{{ $key->name }}</p>
                                <p class="text-xs text-brand-muted">{{ $key->prefix }}•••••••• • {{ implode(', ', $key->abilities ?? []) }}</p>
                                <p class="text-xs text-brand-muted">Last used: {{ $key->last_used_at?->diffForHumans() ?? 'never' }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full px-2 py-1 text-xs font-bold {{ $key->isUsable() ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted' }}">{{ $key->isUsable() ? 'Active' : 'Revoked/expired' }}</span>
                                @if($key->isUsable())
                                    <form method="POST" action="{{ route('dashboard.api-keys.destroy', $key) }}">@csrf @method('DELETE')<button class="text-sm font-bold text-brand-error">Revoke</button></form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
