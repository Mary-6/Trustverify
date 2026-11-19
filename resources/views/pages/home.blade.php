@extends('layouts.site')

@section('title', 'Trustverifysms | SMM & Virtual Numbers')
@section('description', 'Buy worldwide SMM services — followers, likes, shares, subscribers and page followers — plus virtual numbers for SMS verification, with secure XAF wallet checkout.')

@section('content')
<section class="relative overflow-hidden bg-brand-navy text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #16C7B7 0, transparent 35%), radial-gradient(circle at 80% 10%, #F4B942 0, transparent 28%), radial-gradient(circle at 60% 80%, #16C7B7 0, transparent 30%);"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
        <div class="max-w-3xl">
            <p class="inline-flex rounded-full border border-brand-teal/40 bg-brand-teal/10 px-4 py-1 text-sm font-bold text-brand-teal">Live SMM catalogue · virtual numbers</p>
            <h1 class="mt-6 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">Grow your socials with verified SMM services.</h1>
            <p class="mt-6 text-lg leading-8 text-slate-300">Worldwide followers, likes, shares, subscribers and page followers across Instagram, TikTok, Facebook, YouTube, Telegram and WhatsApp — plus virtual numbers for SMS verification.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Explore SMM services</a>
                <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 px-6 py-3 text-sm font-extrabold text-white hover:bg-white/10">Virtual numbers</a>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><p class="text-2xl font-extrabold text-brand-teal">{{ number_format($stats['smm_products']) }}</p><p class="text-sm text-slate-300">Active SMM products</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><p class="text-2xl font-extrabold text-brand-teal">{{ number_format($stats['brands']) }}</p><p class="text-sm text-slate-300">Brands covered</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><p class="text-2xl font-extrabold text-brand-teal">XAF</p><p class="text-sm text-slate-300">Wallet + secure checkout</p></div>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Live catalogue</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy">Pick a brand, then choose your service.</h2>
            <p class="mt-2 max-w-2xl text-brand-muted">Product counts update from the active catalogue. Choose a brand to see followers, likes, shares, subscribers and related services.</p>
        </div>
        <a href="{{ route('services.index') }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">View all services →</a>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($brandStats as $brand)
            <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $brand['slug']]) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-brand-teal/40 hover:shadow-xl">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-brand-navy group-hover:text-brand-teal">{{ $brand['name'] }}</h3>
                        <p class="mt-1 text-sm text-brand-muted">{{ number_format($brand['products_count']) }} services</p>
                    </div>
                    <span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">Live</span>
                </div>
                <p class="mt-4 text-sm font-semibold text-brand-teal">Open brand services →</p>
            </a>
        @endforeach

        <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="group rounded-2xl border border-dashed border-brand-teal/40 bg-brand-teal-soft/40 p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-extrabold text-brand-navy group-hover:text-brand-teal">Virtual Numbers</h3>
                    <p class="mt-1 text-sm text-brand-muted">US SMS verification numbers</p>
                </div>
                <span class="rounded-full {{ $stats['virtual_numbers'] > 0 ? 'bg-brand-teal-soft text-brand-navy' : 'bg-white text-brand-teal' }} px-2 py-1 text-xs font-bold">{{ $stats['virtual_numbers'] > 0 ? 'Live' : 'Soon' }}</span>
            </div>
            <p class="mt-4 text-sm font-semibold text-brand-teal">{{ $stats['virtual_numbers'] > 0 ? 'View numbers →' : 'See availability →' }}</p>
        </a>

    </div>
</section>

<section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Fresh from the catalogue</p>
                <h2 class="mt-2 text-3xl font-extrabold text-brand-navy">Latest active SMM products.</h2>
            </div>
            <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">Browse all SMM →</a>
        </div>

        @if($featuredProducts->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-brand-bg p-10 text-center text-brand-muted">No active SMM products are published yet.</div>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($featuredProducts as $product)
                    <a href="{{ route('services.show', $product) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-brand-teal">{{ $product->category?->name ?? 'SMM Service' }}</p>
                            <span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">Live</span>
                        </div>
                        <h3 class="mt-3 line-clamp-2 text-lg font-extrabold text-brand-navy">{{ $product->name }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm text-brand-muted">{{ $product->summary }}</p>
                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                            <span class="text-sm font-bold text-brand-navy">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</span>
                            <span class="text-xs font-semibold text-brand-muted">Min {{ $product->min_order ?? 1 }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-2">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Why Trustverifysms</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy">Built for trust, speed and control.</h2>
            <div class="mt-8 space-y-5">
                <div class="rounded-2xl border border-slate-200 p-5"><h3 class="font-extrabold text-brand-navy">Transparent wallet checkout</h3><p class="mt-2 text-sm text-brand-muted">Add funds, review balances and place orders without hidden charges.</p></div>
                <div class="rounded-2xl border border-slate-200 p-5"><h3 class="font-extrabold text-brand-navy">Live catalogue counts</h3><p class="mt-2 text-sm text-brand-muted">Product availability and pricing are shown from the active catalogue before you order.</p></div>
                <div class="rounded-2xl border border-slate-200 p-5"><h3 class="font-extrabold text-brand-navy">Security by design</h3><p class="mt-2 text-sm text-brand-muted">Role-based admin access, encrypted delivery data, audit logs and API key controls.</p></div>
            </div>
        </div>
        <div class="rounded-3xl bg-brand-navy p-8 text-white">
            <h2 class="text-2xl font-extrabold">How it works</h2>
            <ol class="mt-6 space-y-5">
                <li class="flex gap-4"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-teal text-sm font-extrabold text-brand-navy">1</span><div><p class="font-bold">Create your account</p><p class="text-sm text-slate-300">Register and verify your email to unlock checkout.</p></div></li>
                <li class="flex gap-4"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-teal text-sm font-extrabold text-brand-navy">2</span><div><p class="font-bold">Fund your wallet</p><p class="text-sm text-slate-300">Choose a supported payment method and wait for verification.</p></div></li>
                <li class="flex gap-4"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-teal text-sm font-extrabold text-brand-navy">3</span><div><p class="font-bold">Place orders</p><p class="text-sm text-slate-300">Pick a brand and service, enter the required link/details and confirm with your wallet.</p></div></li>
                <li class="flex gap-4"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-gold text-sm font-extrabold text-brand-navy">4</span><div><p class="font-bold">Track delivery</p><p class="text-sm text-slate-300">Monitor order status, delivery data and support tickets.</p></div></li>
            </ol>
        </div>
    </div>
</section>

<section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-brand-teal/20 bg-brand-teal-soft p-8">
                <h2 class="text-2xl font-extrabold text-brand-navy">Security and privacy</h2>
                <p class="mt-3 text-sm leading-7 text-brand-text">Sensitive delivery data is encrypted, wallet movements are transactional, supplier activity is logged, and admin actions are auditable.</p>
                <ul class="mt-5 space-y-3 text-sm text-brand-text">
                    <li class="flex gap-3"><span class="text-brand-teal font-extrabold">✓</span> Wallet balance checked before every purchase.</li>
                    <li class="flex gap-3"><span class="text-brand-teal font-extrabold">✓</span> Order idempotency prevents duplicate charges.</li>
                    <li class="flex gap-3"><span class="text-brand-teal font-extrabold">✓</span> API keys are hashed and can be revoked.</li>
                </ul>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-8">
                <h2 class="text-2xl font-extrabold text-brand-navy">Frequently asked questions</h2>
                <div class="mt-5 space-y-4">
                    <details class="rounded-2xl border border-slate-200 p-4"><summary class="cursor-pointer font-bold text-brand-navy">How do I add funds?</summary><p class="mt-2 text-sm text-brand-muted">Create an account, open Wallet, choose a payment method and submit a deposit request. Funds are credited after verification.</p></details>
                    <details class="rounded-2xl border border-slate-200 p-4"><summary class="cursor-pointer font-bold text-brand-navy">Are SMM services instant?</summary><p class="mt-2 text-sm text-brand-muted">Delivery speed depends on the service and supplier. Each product page shows its status and order limits before purchase.</p></details>
                    <details class="rounded-2xl border border-slate-200 p-4"><summary class="cursor-pointer font-bold text-brand-navy">When will virtual numbers be available?</summary><p class="mt-2 text-sm text-brand-muted">Virtual numbers are being prepared for SMS verification. We will only publish them when supplier coverage and pricing are verified.</p></details>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
