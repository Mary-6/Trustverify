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
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Availability</dt><dd class="mt-1 text-sm font-semibold">{{ $product->isAvailable() ? 'Available' : 'Unavailable' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-brand-muted">Supplier mode</dt><dd class="mt-1 text-sm font-semibold">{{ $product->supplier_adapter ?: $product->supplier ?: 'manual' }}</dd></div>
                </dl>
            </div>

            @if($product->description)
                <div class="mt-8 prose prose-slate max-w-none text-sm leading-7 text-brand-text">{!! nl2br(e($product->description)) !!}</div>
            @endif
            @if($product->terms)
                <div class="mt-8 rounded-2xl border border-brand-gold/30 bg-brand-gold/10 p-5">
                    <h2 class="font-extrabold text-brand-navy">Terms</h2>
                    <p class="mt-2 text-sm leading-6 text-brand-text">{!! nl2br(e($product->terms)) !!}</p>
                </div>
            @endif
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-extrabold text-brand-navy">Order this product</h2>
            <div class="mt-4 rounded-2xl bg-brand-navy p-5 text-white">
                <p class="text-sm text-slate-300">Price</p>
                <p class="mt-1 text-2xl font-extrabold">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</p>
            </div>
            @if($product->isAvailable())
                @auth
                    <a href="{{ route('dashboard.orders.create', $product) }}" class="mt-5 block rounded-xl bg-brand-teal px-5 py-3 text-center text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Purchase now</a>
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
</section>
@endsection
