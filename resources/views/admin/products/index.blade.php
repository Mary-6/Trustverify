@extends('layouts.admin')

@section('title', 'Admin Products | TrustVerifySMS')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-brand-navy">Products</h1>
            <p class="mt-1 text-brand-muted">Manage catalogue items, supplier mappings and visibility.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Add product</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="mb-5 flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
            <select name="type" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
                <option value="">All types</option>
                @foreach(['smm_service','virtual_number'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ str_replace('_', ' ', $type) }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border-slate-300 px-4 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach(['draft','active','inactive','archived'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-brand-muted"><th class="pb-3">Product</th><th class="pb-3">Type</th><th class="pb-3">Price</th><th class="pb-3">Supplier</th><th class="pb-3">Status</th><th class="pb-3">Active</th><th class="pb-3"></th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="border-t border-slate-100">
                            <td class="py-3 font-semibold">{{ Str::limit($product->name, 45) }}</td>
                            <td class="py-3">{{ str_replace('_', ' ', $product->type) }}</td>
                            <td class="py-3 font-semibold">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</td>
                            <td class="py-3 text-brand-muted">{{ $product->supplier_adapter ?: $product->supplier ?: 'manual' }}</td>
                            <td class="py-3"><span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ $product->status }}</span></td>
                            <td class="py-3">{{ $product->is_active ? 'Yes' : 'No' }}</td>
                            <td class="py-3 flex gap-3"><a href="{{ route('admin.products.edit', $product) }}" class="font-bold text-brand-teal">Edit</a><form method="POST" action="{{ route('admin.products.destroy', $product) }}">@csrf @method('DELETE')<button class="font-bold text-brand-error">Archive</button></form></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-brand-muted">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $products->links() }}</div>
    </div>
</div>
@endsection
