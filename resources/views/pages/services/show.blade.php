@extends('layouts.site')

@section('title', $product->name.' | TrustVerifySMS')
@section('description', $product->summary ?: 'View details and purchase '.$product->name.' on TrustVerifySMS.')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ $backUrl }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← Back to services</a>
    <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_380px]">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">{{ $product->category?->name ?? str_replace('_', ' ', $product->type) }}</p>
            <h1 class="mt-3 text-3xl font-extrabold text-brand-navy">{{ $product->name }}</h1>
            <p class="mt-4 text-brand-muted">{{ $product->summary }}</p>

            <div class="mt-8 rounded-2xl bg-brand-bg p-5">
                <h2 class="font-extrabold text-brand-navy">Product details</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Type</dt><dd class="mt-1 text-sm font-semibold">{{ str_replace('_', ' ', $product->type) }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Price</dt><dd class="mt-1 text-sm font-semibold">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Minimum order</dt><dd class="mt-1 text-sm font-semibold">{{ $product->min_order ?? 1 }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Maximum order</dt><dd class="mt-1 text-sm font-semibold">{{ $product->max_order ?? 'Unlimited' }}</dd></div>
                    @if($delivery = $product->estimatedDelivery())<div><dt class="text-xs font-bold uppercase text-brand-muted">Est. delivery</dt><dd class="mt-1 text-sm font-semibold">{{ $delivery }}</dd></div>@endif
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Availability</dt><dd class="mt-1 text-sm font-semibold">{{ $product->isAvailable() ? 'Available' : 'Unavailable' }}</dd></div>
                </dl>
            </div>

            @if($description = $product->customerDescription())
                <div class="mt-8 prose prose-slate max-w-none text-sm leading-7 text-brand-text">{!! nl2br(e($description)) !!}</div>
            @endif
            @if($terms = $product->customerTerms())
                <div class="mt-8 rounded-2xl border border-brand-gold/30 bg-brand-gold/10 p-5">
                    <h2 class="font-extrabold text-brand-navy">Terms</h2>
                    <p class="mt-2 text-sm leading-6 text-brand-text">{!! nl2br(e($terms)) !!}</p>
                </div>
            @endif
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-extrabold text-brand-navy">Order this product</h2>
            <div class="mt-4 rounded-2xl bg-brand-navy p-5 text-white">
                <p class="text-sm text-slate-300">Price</p>
                <p class="mt-1 text-2xl font-extrabold">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</p>
            </div>
            @if($product->isAvailable() && $product->type === 'smm_service')
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4" data-product-quote
                     data-unit-price="{{ (float) $product->price }}"
                     data-pricing-unit="{{ $product->pricingUnit() }}"
                     data-currency="{{ $product->currency ?: 'XAF' }}"
                     data-decimals="{{ $product->priceDecimals() }}"
                     data-min="{{ (int) ($product->min_order ?? 1) }}"
                     data-max="{{ $product->max_order ? (int) $product->max_order : 1000000 }}">
                    <label class="text-sm font-bold text-brand-text" for="product-quantity">Enter Amount</label>
                    <input id="product-quantity" type="number" min="{{ (int) ($product->min_order ?? 1) }}" max="{{ $product->max_order ? (int) $product->max_order : 1000000 }}" value="{{ (int) ($product->min_order ?? 1) }}" class="mt-3 w-full rounded-xl border-slate-300 bg-white px-4 py-3">
                    <div class="mt-4 flex items-center justify-between gap-4 rounded-xl bg-white p-3">
                        <div><p class="text-xs text-brand-muted">Estimated total</p><p id="product-total" class="text-lg font-extrabold text-brand-navy">—</p></div>
                        <div class="text-right"><p class="text-xs text-brand-muted">Price</p><p class="text-sm font-bold text-brand-navy">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency ?: 'XAF' }}{{ $product->priceSuffix() }}</p></div>
                    </div>
                    <p class="mt-2 text-xs text-brand-muted">Min {{ number_format($product->min_order ?? 1) }} · Max {{ $product->max_order ? number_format($product->max_order) : 'Unlimited' }}{{ $product->isPer1000() ? ' · priced per 1,000' : '' }}</p>
                </div>
            @endif
            @if($product->isAvailable())
                @auth
                    <a id="purchase-now" href="{{ route('dashboard.orders.create', $product) }}" class="mt-5 block rounded-xl bg-brand-teal px-5 py-3 text-center text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Purchase now</a>
                @else
                    <a href="{{ route('login') }}" class="mt-5 block rounded-xl bg-brand-teal px-5 py-3 text-center text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Sign in to purchase</a>
                    <a href="{{ route('register') }}" class="mt-3 block rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-bold text-brand-navy hover:bg-slate-50">Create account</a>
                @endauth
            @else
                <div class="mt-5 rounded-xl bg-slate-100 px-5 py-3 text-center text-sm font-bold text-brand-muted">Currently unavailable</div>
            @endif
            <ul class="mt-6 space-y-2 text-sm text-brand-muted">
                <li>Wallet checkout protects your balance.</li>
                <li>Order status is tracked in your dashboard.</li>
                <li>Delivery details are encrypted where sensitive.</li>
            </ul>
        </aside>
    </div>

    @if($related->isNotEmpty())
        <div class="mt-12">
            <h2 class="text-2xl font-extrabold text-brand-navy">Related services</h2>
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($related as $item)
                    <a href="{{ route('services.show', $item) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-xl">
                        <h3 class="font-extrabold text-brand-navy">{{ $item->name }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm text-brand-muted">{{ $item->summary }}</p>
                        <p class="mt-4 text-sm font-bold text-brand-teal">{{ number_format((float) $item->price, $item->priceDecimals()) }} {{ $item->currency }}{{ $item->priceSuffix() }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

<script>
(function () {
    const quote = document.querySelector('[data-product-quote]');
    if (!quote) return;
    const quantity = document.getElementById('product-quantity');
    const total = document.getElementById('product-total');
    const purchase = document.getElementById('purchase-now');
    if (!quantity || !total) return;

    const unitPrice = parseFloat(quote.dataset.unitPrice || '0');
    const currency = quote.dataset.currency || 'XAF';
    const per1000 = quote.dataset.pricingUnit === 'per_1000';
    const decimals = parseInt(quote.dataset.decimals || '0', 10);

    function format(value) {
        return value.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + ' ' + currency;
    }

    function currentTotal() {
        const qty = Math.max(0, parseInt(quantity.value || '0', 10) || 0);
        return per1000 ? (unitPrice * qty) / 1000 : unitPrice * qty;
    }

    function update() {
        const qty = Math.max(0, parseInt(quantity.value || '0', 10) || 0);
        total.textContent = qty > 0 ? format(currentTotal()) : '—';
        if (purchase) {
            const url = new URL(purchase.href, window.location.origin);
            if (qty > 0) {
                url.searchParams.set('quantity', String(qty));
            } else {
                url.searchParams.delete('quantity');
            }
            purchase.href = url.pathname + url.search + url.hash;
        }
    }

    quantity.addEventListener('input', update);
    update();
})();
</script>
</section>
@endsection
