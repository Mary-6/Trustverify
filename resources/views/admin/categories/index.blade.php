@extends('layouts.account')

@section('title', 'Categories | Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-brand-navy">Categories</h1>
            <p class="mt-1 text-brand-muted">Organize products for the public catalogue and filters.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Add category</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-brand-bg text-left text-xs uppercase text-brand-muted">
                <tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">Products</th><th class="px-5 py-3">Sort</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-t border-slate-100">
                        <td class="px-5 py-4"><div class="font-bold text-brand-navy">{{ $category->name }}</div><div class="text-xs text-brand-muted">/{{ $category->slug }}</div></td>
                        <td class="px-5 py-4">{{ $category->type ? str_replace('_', ' ', $category->type) : '—' }}</td>
                        <td class="px-5 py-4">{{ $category->products_count }}</td>
                        <td class="px-5 py-4">{{ $category->sort_order }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-bold {{ $category->is_active ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted' }}">{{ $category->is_active ? 'active' : 'inactive' }}</span></td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="font-bold text-brand-teal hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="mt-2 inline" onsubmit="return confirm('Delete or deactivate this category?')">@csrf @method('DELETE')<button class="font-bold text-brand-error hover:underline">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-brand-muted">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $categories->links() }}
</div>
@endsection
