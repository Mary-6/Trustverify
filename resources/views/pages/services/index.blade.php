@extends('layouts.site')

@section('title', 'SMM & Virtual Number Services | Trustverifysms')
@section('description', 'Choose a platform, service and quality option for worldwide SMM growth and virtual numbers for SMS verification with secure XAF wallet checkout.')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Catalogue</p>
            <h1 class="mt-2 text-4xl font-extrabold text-brand-navy">What do you want to grow?</h1>
            <p class="mt-3 max-w-2xl text-brand-muted">Pick a platform, choose the service you need, then select a quality option and quantity. Prices are shown per 1,000 where applicable.</p>
        </div>
        @if($selectedService !== 'smm_service' || $selectedGroup)
            <form method="GET" action="{{ route('services.index') }}" class="flex w-full max-w-xl gap-2">
                <input type="hidden" name="service" value="{{ $selectedService }}">
                @if($selectedService === 'smm_service' && $selectedBrandSlug)<input type="hidden" name="brand" value="{{ $selectedBrandSlug }}">@endif
                @if($selectedGroupSlug)<input type="hidden" name="group" value="{{ $selectedGroupSlug }}">@endif
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search services..." class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal">
                <button class="rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Search</button>
            </form>
        @endif
    </div>

    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('services.index') }}">
            <label for="service" class="text-sm font-bold text-brand-navy">Service</label>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                <select id="service" name="service" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal" onchange="this.form.submit()">
                    @foreach($serviceOptions as $option)
                        <option value="{{ $option['slug'] }}" @selected($selectedService === $option['slug'])>{{ $option['label'] }}{{ $option['available'] ? '' : ' (Coming soon)' }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Apply</button>
            </div>
        </form>
    </div>

    @if($selectedService === 'smm_service')
        @if($awaitingBrand)
            <div class="mt-8">
                <div class="mb-5">
                    <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Step 1</p>
                    <h2 class="mt-1 text-2xl font-extrabold text-brand-navy">Choose a platform</h2>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($brandOptions as $brand)
                        @if($brand['available'])
                            <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $brand['slug']]) }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-brand-teal/50 hover:shadow-xl">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-xl font-extrabold text-brand-navy group-hover:text-brand-teal">{{ $brand['label'] }}</h3>
                                    <span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">Live</span>
                                </div>
                                <p class="mt-2 text-sm text-brand-muted">{{ number_format($brand['product_count']) }} services available</p>
                                <p class="mt-5 text-sm font-bold text-brand-teal">Choose service →</p>
                            </a>
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 opacity-80">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-xl font-extrabold text-brand-navy">{{ $brand['label'] }}</h3>
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-brand-muted">Coming soon</span>
                                </div>
                                <p class="mt-2 text-sm text-brand-muted">We are preparing price-checked services for this platform.</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @elseif($brandComingSoon)
            <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Coming soon</p>
                <h2 class="mt-3 text-3xl font-extrabold text-brand-navy">{{ $selectedBrand['label'] }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-brand-muted">Price-checked services for this platform are not available yet. We will publish them only when the catalogue and pricing are verified.</p>
                <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="mt-6 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Choose another platform</a>
            </div>
        @elseif($groupComingSoon)
            <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Coming soon</p>
                <h2 class="mt-3 text-3xl font-extrabold text-brand-navy">{{ $selectedGroup['label'] }} on {{ $selectedBrand['label'] }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-brand-muted">This service is not available yet at a verified price. We will only show it when supplier pricing is confirmed.</p>
                <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $selectedBrandSlug]) }}" class="mt-6 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Back to {{ $selectedBrand['label'] }} services</a>
            </div>
        @elseif($selectedGroup)
            <div class="mt-8">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Step 3 · Option</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-brand-navy">Choose an option</h2>
                        <p class="mt-1 text-sm text-brand-muted">{{ $selectedGroup['label'] }} on {{ $selectedBrand['label'] }} — {{ number_format($products->total()) }} option{{ $products->total() === 1 ? '' : 's' }} found</p>
                    </div>
                    <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $selectedBrandSlug]) }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← All {{ $selectedBrand['label'] }} services</a>
                </div>

                <div class="mb-6 flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-brand-navy">1 · {{ $selectedBrand['label'] }}</span>
                    <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-brand-navy">2 · {{ $selectedGroup['label'] }}</span>
                    <span class="rounded-full bg-brand-navy px-3 py-1 text-white">3 · Choose option</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-brand-muted">4 · Quantity & checkout</span>
                </div>

                @if($products->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-brand-muted">No options match your search.</div>
                @else
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <a href="{{ route('services.show', $product) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-bold uppercase tracking-wider text-brand-teal">{{ $selectedBrand['label'] }}</p>
                                    <span class="rounded-full px-2 py-1 text-xs font-bold {{ $product->isAvailable() ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted' }}">{{ $product->isAvailable() ? 'Available' : 'Unavailable' }}</span>
                                </div>
                                <h3 class="mt-3 line-clamp-2 text-lg font-extrabold text-brand-navy">{{ $product->name }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm text-brand-muted">{{ $product->summary }}</p>
                                <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
                                    <div class="flex items-center justify-between"><span class="text-brand-muted">Price</span><span class="font-extrabold text-brand-navy">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</span></div>
                                    <div class="flex items-center justify-between"><span class="text-brand-muted">Minimum</span><span class="font-semibold text-brand-text">{{ number_format($product->min_order ?? 1) }}</span></div>
                                    <div class="flex items-center justify-between"><span class="text-brand-muted">Maximum</span><span class="font-semibold text-brand-text">{{ $product->max_order ? number_format($product->max_order) : 'Unlimited' }}</span></div>
                                </div>
                                <p class="mt-4 text-sm font-bold text-brand-teal">Choose option →</p>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $products->links() }}</div>
                @endif
            </div>
        @else
            <div class="mt-8">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Step 2</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-brand-navy">What do you need on {{ $selectedBrand['label'] }}?</h2>
                        <p class="mt-1 text-sm text-brand-muted">Choose one service category. Services marked coming soon are not published until verified pricing is available.</p>
                    </div>
                    <div class="flex gap-2 text-xs font-bold">
                        <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-brand-navy">1 · {{ $selectedBrand['label'] }}</span>
                        <span class="rounded-full bg-brand-navy px-3 py-1 text-white">2 · Service</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-brand-muted">3 · Option</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-brand-muted">4 · Quantity</span>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($brandSummaries as $summary)
                        @if($summary['available'])
                            <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $selectedBrandSlug, 'group' => $summary['slug']]) }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-brand-teal/50 hover:shadow-xl">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-lg font-extrabold text-brand-navy group-hover:text-brand-teal">{{ $summary['label'] }}</h3>
                                    <span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">{{ number_format($summary['count']) }}</span>
                                </div>
                                <p class="mt-2 line-clamp-2 text-sm text-brand-muted">{{ $summary['description'] }}</p>
                                <p class="mt-4 text-sm font-bold text-brand-navy">from {{ number_format((float) $summary['min_price'], 2) }} XAF / 1,000</p>
                                <p class="mt-3 text-sm font-bold text-brand-teal">View options →</p>
                            </a>
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 opacity-80">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-lg font-extrabold text-brand-navy">{{ $summary['label'] }}</h3>
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-brand-muted">Soon</span>
                                </div>
                                <p class="mt-2 line-clamp-2 text-sm text-brand-muted">{{ $summary['description'] }}</p>
                                <p class="mt-4 text-sm font-semibold text-brand-muted">Verified pricing pending</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @elseif($comingSoon)
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Coming soon</p>
            <h2 class="mt-3 text-3xl font-extrabold text-brand-navy">{{ $selectedServiceLabel }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-brand-muted">This service is not available yet. We are preparing verified inventory and pricing, and it will appear here as soon as it is ready.</p>
            <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="mt-6 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Browse SMM services</a>
        </div>
    @else
        <div class="mt-8 grid gap-8 lg:grid-cols-[240px_1fr]">
            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-extrabold text-brand-navy">Categories</h2>
                <form method="GET" class="mt-4 space-y-2">
                    <input type="hidden" name="service" value="{{ $selectedService }}">
                    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                    <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-brand-bg">
                        <input type="radio" name="category_id" value="" {{ ! request('category_id') ? 'checked' : '' }} class="text-brand-teal focus:ring-brand-teal">
                        <span>All categories</span>
                    </label>
                    @foreach($categories as $category)
                        <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-brand-bg">
                            <input type="radio" name="category_id" value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'checked' : '' }} class="text-brand-teal focus:ring-brand-teal">
                            <span>{{ $category->name }}</span>
                        </label>
                    @endforeach
                    <button class="mt-3 w-full rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Apply</button>
                </form>
            </aside>

            <div>
                @if($products->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-brand-muted">No active products match your filters.</div>
                @else
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <a href="{{ route('services.show', $product) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-bold uppercase tracking-wider text-brand-teal">{{ $product->category?->name ?? str_replace('_', ' ', $product->type) }}</p>
                                    <span class="rounded-full px-2 py-1 text-xs font-bold {{ $product->isAvailable() ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted' }}">{{ $product->isAvailable() ? 'Available' : 'Unavailable' }}</span>
                                </div>
                                <h3 class="mt-3 text-lg font-extrabold text-brand-navy">{{ $product->name }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm text-brand-muted">{{ $product->summary }}</p>
                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                    <span class="text-sm font-bold text-brand-navy">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</span>
                                    <span class="text-xs font-semibold text-brand-muted">Min {{ $product->min_order ?? 1 }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $products->links() }}</div>
                @endif
            </div>
        </div>
    @endif
</section>
@endsection
