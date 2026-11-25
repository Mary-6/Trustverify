@extends('layouts.admin')

@section('title', 'Service Management | TrustVerifySMS Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Catalogue control</p>
            <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">Service management</h1>
            <p class="mt-2 text-sm text-brand-muted">Control which services customers can see. Disabled services are kept but hidden, and supplier sync will not reactivate them.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-brand-teal hover:text-brand-navy">← Suppliers</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[320px_1fr]">
        <aside class="space-y-6">
            <form method="POST" action="{{ route('admin.service-management.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5">
                @csrf @method('PATCH')
                <h2 class="font-extrabold text-brand-navy">JustAnotherPanel import limit</h2>
                <p class="mt-1 text-xs text-brand-muted">0 means no limit. This only affects JAP sync/import.</p>
                <input type="number" name="service_limit" min="0" max="5000" value="{{ old('service_limit', $serviceLimit) }}" class="mt-3 w-full rounded-xl border-slate-300 px-4 py-3">
                <button class="mt-3 w-full rounded-xl bg-brand-navy px-4 py-3 text-sm font-extrabold text-white hover:bg-brand-teal">Save JAP limit</button>
            </form>

            <form method="GET" action="{{ route('admin.service-management.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-extrabold text-brand-navy">Filters</h2>
                <div class="mt-3 space-y-3">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or service ID" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm">
                    <select name="supplier" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm">
                        <option value="">All suppliers</option>
                        @foreach($suppliers as $key => $label)
                            <option value="{{ $key }}" @selected($supplier === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="category_id" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select name="state" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm">
                        <option value="all" @selected($state === 'all')>All states</option>
                        <option value="active" @selected($state === 'active')>Active only</option>
                        <option value="disabled" @selected($state === 'disabled')>Disabled only</option>
                        <option value="archived" @selected($state === 'archived')>Archived only</option>
                    </select>
                    <button class="w-full rounded-xl bg-brand-teal px-4 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Apply filters</button>
                </div>
            </form>
        </aside>

        <form method="POST" action="{{ route('admin.service-management.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5">
            @csrf @method('PATCH')
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-extrabold text-brand-navy">Selected services</h2>
                    <p class="text-xs text-brand-muted">{{ $products->total() }} service{{ $products->total() === 1 ? '' : 's' }} found.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button name="action" value="activate" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-extrabold text-white hover:bg-emerald-700">Activate selected</button>
                    <button name="action" value="deactivate" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-extrabold text-white hover:bg-amber-600">Deactivate selected</button>
                </div>
            </div>

            @error('selected_ids')<p class="mt-3 text-sm font-semibold text-brand-error">{{ $message }}</p>@enderror

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-extrabold uppercase tracking-wider text-brand-muted dark:bg-slate-900/60">
                        <tr>
                            <th class="px-3 py-3"><input type="checkbox" onclick="document.querySelectorAll('.service-select').forEach(cb => cb.checked = this.checked)" class="rounded text-brand-teal"></th>
                            <th class="px-3 py-3">ID</th>
                            <th class="px-3 py-3">Supplier</th>
                            <th class="px-3 py-3">Supplier ID</th>
                            <th class="px-3 py-3">Service</th>
                            <th class="px-3 py-3">Category</th>
                            <th class="px-3 py-3">Price</th>
                            <th class="px-3 py-3">Min / Max</th>
                            <th class="px-3 py-3">State</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($products as $product)
                            @php $supplierKey = $product->supplier_adapter ?: $product->supplier ?: 'manual'; @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40">
                                <td class="px-3 py-3"><input type="checkbox" name="selected_ids[]" value="{{ $product->id }}" class="service-select rounded text-brand-teal"></td>
                                <td class="px-3 py-3 font-bold">{{ $product->id }}</td>
                                <td class="px-3 py-3 text-brand-muted">{{ $suppliers[$supplierKey] ?? ucfirst($supplierKey) }}</td>
                                <td class="px-3 py-3 font-mono text-xs">{{ $product->supplier_product_id ?: '—' }}</td>
                                <td class="max-w-md px-3 py-3">
                                    <p class="font-bold text-brand-navy dark:text-white">{{ $product->name }}</p>
                                    <p class="text-xs text-brand-muted">{{ \Illuminate\Support\Str::limit($product->summary, 90) }}</p>
                                </td>
                                <td class="px-3 py-3 text-brand-muted">{{ $product->category?->name ?? '—' }}</td>
                                <td class="px-3 py-3 font-bold">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</td>
                                <td class="px-3 py-3 text-brand-muted">{{ number_format($product->min_order ?? 1) }} / {{ $product->max_order ? number_format($product->max_order) : '∞' }}</td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-extrabold {{ $product->isAvailable() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">{{ $product->isAvailable() ? 'Active' : 'Disabled' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-3 py-12 text-center text-brand-muted">No services found. Run supplier sync after configuring suppliers.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $products->links() }}</div>
        </form>
    </div>
</div>
@endsection
