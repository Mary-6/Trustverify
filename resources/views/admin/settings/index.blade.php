@extends('layouts.account')

@section('title', 'Admin Settings | Trustverifysms')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-brand-navy">Settings</h1>
        <p class="mt-1 text-brand-muted">General, pricing, payment and supplier controls.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf @method('PATCH')
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">General</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div><label class="text-sm font-bold text-brand-text">Site name</label><input name="site_name" value="{{ old('site_name', $settings['general']['site_name']) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
                <div><label class="text-sm font-bold text-brand-text">Support email</label><input name="support_email" type="email" value="{{ old('support_email', $settings['general']['support_email']) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
                <div><label class="text-sm font-bold text-brand-text">Primary currency</label><input name="primary_currency" value="{{ old('primary_currency', $settings['general']['primary_currency']) }}" maxlength="3" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">Pricing</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><label class="text-sm font-bold text-brand-text">Global markup %</label><input name="global_markup_percent" type="number" step="0.01" min="0" value="{{ old('global_markup_percent', $settings['pricing']['global_markup_percent']) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
                <div><label class="text-sm font-bold text-brand-text">Global fixed markup</label><input name="global_markup_fixed" type="number" step="0.01" min="0" value="{{ old('global_markup_fixed', $settings['pricing']['global_markup_fixed']) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">Payments</h2>
            <div class="mt-4">
                <label class="text-sm font-bold text-brand-text">Enabled methods (comma separated)</label>
                <input name="payment_methods" value="{{ old('payment_methods', $settings['payments']['methods']) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="mtn_momo, orange_money, card">
                <p class="mt-2 text-xs text-brand-muted">Gateway credentials remain environment variables; this controls which methods customers can request.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">Supplier</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm font-semibold"><input type="checkbox" name="manual_fulfillment_enabled" value="1" {{ old('manual_fulfillment_enabled', $settings['supplier']['manual_fulfillment_enabled']) ? 'checked' : '' }} class="rounded text-brand-teal"> Manual fulfillment enabled</label>
                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm font-semibold"><input type="checkbox" name="faussms_enabled" value="1" {{ old('faussms_enabled', $settings['supplier']['faussms_enabled']) ? 'checked' : '' }} class="rounded text-brand-teal"> FAUSSMS adapter enabled</label>
            </div>
            <p class="mt-3 text-xs text-brand-muted">FAUSSMS endpoints must be configured from authorized API documentation before live submission is enabled.</p>
        </div>

        <button class="rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Save settings</button>
    </form>
</div>
@endsection
