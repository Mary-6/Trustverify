@extends('layouts.account')

@section('title', ($country->exists ? 'Edit' : 'Add').' Country | Admin')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-3xl font-extrabold text-brand-navy">{{ $country->exists ? 'Edit country' : 'Add country' }}</h1>
    <form method="POST" action="{{ $country->exists ? route('admin.countries.update', $country) : route('admin.countries.store') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($country->exists) @method('PATCH') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label class="text-sm font-bold text-brand-text">Name</label><input name="name" value="{{ old('name', $country->name) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
            <div><label class="text-sm font-bold text-brand-text">ISO code</label><input name="code" value="{{ old('code', $country->code) }}" maxlength="2" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="CM"></div>
            <div><label class="text-sm font-bold text-brand-text">Dial code</label><input name="dial_code" value="{{ old('dial_code', $country->dial_code) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="+237"></div>
            <div><label class="text-sm font-bold text-brand-text">Flag</label><input name="flag" value="{{ old('flag', $country->flag) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Emoji or code"></div>
            <div><label class="text-sm font-bold text-brand-text">Currency</label><input name="currency" value="{{ old('currency', $country->currency) }}" maxlength="3" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="XAF"></div>
            <div><label class="text-sm font-bold text-brand-text">Sort order</label><input name="sort_order" type="number" min="0" value="{{ old('sort_order', $country->sort_order ?: 0) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
            <label class="flex items-center gap-2 self-end rounded-xl border border-slate-200 p-3 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $country->exists ? $country->is_active : true) ? 'checked' : '' }} class="rounded text-brand-teal"> Active</label>
        </div>
        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Save country</button>
            <a href="{{ route('admin.countries.index') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-brand-navy">Cancel</a>
        </div>
    </form>
</div>
@endsection
