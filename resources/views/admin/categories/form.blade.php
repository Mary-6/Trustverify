@extends('layouts.account')

@section('title', ($category->exists ? 'Edit' : 'Add').' Category | Admin')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-3xl font-extrabold text-brand-navy">{{ $category->exists ? 'Edit category' : 'Add category' }}</h1>
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($category->exists) @method('PATCH') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label class="text-sm font-bold text-brand-text">Name</label><input name="name" value="{{ old('name', $category->name) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
            <div><label class="text-sm font-bold text-brand-text">Slug</label><input name="slug" value="{{ old('slug', $category->slug) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Auto-generated if empty"></div>
            <div><label class="text-sm font-bold text-brand-text">Type</label><select name="type" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"><option value="">General</option>@foreach(['smm_service','virtual_number'] as $type)<option value="{{ $type }}" @selected(old('type', $category->type) === $type)>{{ str_replace('_', ' ', $type) }}</option>@endforeach</select></div>
            <div><label class="text-sm font-bold text-brand-text">Icon</label><input name="icon" value="{{ old('icon', $category->icon) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Optional icon class or emoji"></div>
            <div><label class="text-sm font-bold text-brand-text">Sort order</label><input name="sort_order" type="number" min="0" value="{{ old('sort_order', $category->sort_order ?: 0) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></div>
            <label class="flex items-center gap-2 self-end rounded-xl border border-slate-200 p-3 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->exists ? $category->is_active : true) ? 'checked' : '' }} class="rounded text-brand-teal"> Active</label>
            <div class="sm:col-span-2"><label class="text-sm font-bold text-brand-text">Description</label><textarea name="description" rows="4" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">{{ old('description', $category->description) }}</textarea></div>
        </div>
        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Save category</button>
            <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-brand-navy">Cancel</a>
        </div>
    </form>
</div>
@endsection
