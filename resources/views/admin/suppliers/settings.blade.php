@extends('layouts.admin')

@section('title', 'HeroSMS Settings | TrustVerifySMS Admin')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Supplier configuration</p>
            <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">HeroSMS settings</h1>
            <p class="mt-2 text-sm text-brand-muted">API credentials are stored encrypted server-side and are never shown again.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-brand-teal hover:text-brand-navy">← Suppliers</a>
    </div>

    <form method="POST" action="{{ route('admin.suppliers.settings.update', 'herosms') }}" class="space-y-6">
        @csrf @method('PATCH')

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <label class="flex items-center gap-3 text-sm font-bold text-brand-text">
                <input type="checkbox" name="enabled" value="1" {{ old('enabled', $values['enabled']) ? 'checked' : '' }} class="rounded text-brand-teal">
                Enable HeroSMS for new orders
            </label>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">Connection</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="text-sm font-bold text-brand-text">Base URL</label>
                        <input name="base_url" type="url" value="{{ old('base_url', $values['base_url']) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="https://hero-sms.com/api/v1" required>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-brand-text">Timeout (seconds)</label>
                        <input name="timeout" type="number" min="5" max="120" value="{{ old('timeout', $values['timeout']) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" required>
                    </div>
                    <p class="text-xs text-brand-muted">Current status: <span class="font-bold {{ $apiKeyConfigured ? 'text-emerald-600' : 'text-red-600' }}">{{ $apiKeyConfigured ? 'API key configured' : 'API key missing' }}</span></p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="font-extrabold text-brand-navy">API key</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="text-sm font-bold text-brand-text">HeroSMS API key</label>
                        <input name="api_key" type="password" autocomplete="new-password" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="{{ $apiKeyConfigured ? 'Leave blank to keep current key' : 'Enter API key' }}">
                        <p class="mt-2 text-xs text-brand-muted">Stored encrypted in the database. The saved value is never displayed.</p>
                    </div>
                    @if($apiKeyConfigured)
                        <label class="flex items-center gap-2 text-sm font-semibold text-brand-muted">
                            <input type="checkbox" name="clear_api_key" value="1" class="rounded text-brand-error">
                            Clear saved key and use environment fallback
                        </label>
                    @endif
                </div>
            </div>
        </div>

        <button class="rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Save HeroSMS settings</button>
    </form>
</div>
@endsection
