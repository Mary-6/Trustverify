@extends('layouts.site')

@section('title', 'TrustVerifySMS | Premium SMM & Verification Services')
@section('description', 'Buy SMM services for Instagram, TikTok, Facebook, YouTube, Telegram and WhatsApp, plus virtual numbers for SMS verification with secure wallet checkout.')

@section('content')
<section class="relative overflow-hidden bg-brand-navy text-white">
    <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 15% 20%, #13B89A 0, transparent 32%), radial-gradient(circle at 85% 15%, #B6F36B 0, transparent 28%), radial-gradient(circle at 65% 90%, #13B89A 0, transparent 30%);"></div>
    <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-brand-bg to-transparent"></div>
    <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8 lg:pb-28 lg:pt-24">
        <div class="max-w-4xl">
            <p class="inline-flex rounded-full border border-brand-teal/40 bg-brand-teal/10 px-4 py-1 text-sm font-bold text-brand-teal">Cameroon-focused SMM & verification marketplace</p>
            <h1 class="mt-6 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">Grow your social media with fast, affordable services.</h1>
            <p class="mt-6 text-lg leading-8 text-slate-300">Buy followers, likes, views, shares, comments and subscribers for Instagram, TikTok, Facebook, YouTube, Telegram and WhatsApp — plus virtual numbers for SMS verification. Prices and availability are shown before you pay.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy shadow-lg shadow-brand-teal/20 hover:bg-brand-gold">Get started</a>
                <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 px-6 py-3 text-sm font-extrabold text-white backdrop-blur hover:bg-white/10">Explore services</a>
            </div>
            <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"><p class="text-2xl font-extrabold text-brand-teal">10,000+</p><p class="text-sm text-slate-300">SMM Products</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"><p class="text-2xl font-extrabold text-brand-teal">15+</p><p class="text-sm text-slate-300">Platforms Covered</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"><p class="text-2xl font-extrabold text-brand-teal">500+</p><p class="text-sm text-slate-300">Verification Services</p></div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Social Media Services</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy sm:text-4xl">Choose a platform and start growing.</h2>
            <p class="mt-3 text-brand-muted">Every card opens the live catalogue for that platform. Compare services, review requirements and check pricing before checkout.</p>
        </div>
        <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">Browse all SMM services →</a>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($brandStats as $brand)
            <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $brand['slug']]) }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-brand-teal/50 hover:shadow-2xl">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-teal to-brand-gold opacity-80"></div>
                <div class="flex items-start justify-between gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-navy text-white shadow-sm">
                        <img src="https://cdn.simpleicons.org/{{ $brand['logo'] ?? $brand['slug'] }}" alt="{{ $brand['name'] }} logo" class="h-6 w-6" loading="lazy">
                    </span>
                    <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-xs font-bold text-brand-navy">Live</span>
                </div>
                <h3 class="mt-5 text-xl font-extrabold text-brand-navy group-hover:text-brand-teal">{{ $brand['name'] }}</h3>
                <p class="mt-2 min-h-[3.5rem] text-sm leading-6 text-brand-muted">{{ $brand['description'] }}</p>
                <div class="mt-4 rounded-2xl bg-brand-bg px-4 py-3 text-sm">
                    <p class="font-bold text-brand-navy">Popular services</p>
                    <p class="mt-1 text-brand-muted">{{ $brand['service_examples'] ?: 'Followers, likes and engagement' }}</p>
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
                    <span class="font-semibold text-brand-muted">{{ number_format($brand['products_count']) }} products</span>
                    <span class="font-extrabold text-brand-teal">Choose service →</span>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-brand-muted sm:col-span-2 lg:col-span-3">No active SMM services are published yet.</div>
        @endforelse
    </div>

    <div class="mt-6 rounded-3xl border border-brand-teal/30 bg-brand-teal-soft p-6 sm:flex sm:items-center sm:justify-between sm:p-7">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Need SMS verification?</p>
            <p class="mt-1 text-sm text-brand-text">Virtual numbers are available in the same wallet with product details and pricing shown before purchase.</p>
        </div>
        <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="mt-4 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light sm:mt-0">View verification services →</a>
    </div>
</section>

<section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Why choose TrustVerifySMS</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy sm:text-4xl">A cleaner way to order digital growth.</h2>
            <p class="mt-3 text-brand-muted">Built for customers who want clear pricing, reliable delivery and simple account management.</p>
        </div>
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-brand-bg p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft text-brand-navy">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
                </span>
                <h3 class="mt-4 font-extrabold text-brand-navy">Competitive pricing</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Transparent prices are shown before checkout, so you know the cost before confirming.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-navy text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M9 3v18"/><path d="m13 10 2 2 4-4"/></svg>
                </span>
                <h3 class="mt-4 font-extrabold text-brand-navy">Easy ordering</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Pick a platform, choose a service, enter your details and pay from one wallet.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-brand-bg p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft text-brand-navy">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18"/><path d="M12 3v18"/><path d="m19 5-7 7-7-7"/></svg>
                </span>
                <h3 class="mt-4 font-extrabold text-brand-navy">Fast processing</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Supported orders are submitted automatically and status updates are tracked for you.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-gold/20 text-brand-navy">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                </span>
                <h3 class="mt-4 font-extrabold text-brand-navy">Secure payments</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Wallet balances are checked before every order and deposits are verified before credit.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-brand-bg p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft text-brand-navy">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 14 4-4 3 3 5-6"/></svg>
                </span>
                <h3 class="mt-4 font-extrabold text-brand-navy">Order tracking</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Follow status, supplier references, delivery details and history from your dashboard.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-navy text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
                <h3 class="mt-4 font-extrabold text-brand-navy">Customer support</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Open tickets with your order ID and get help from the support team.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-brand-bg p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal-soft text-brand-navy">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2V4h14v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12h4v4h-4"/></svg>
                </span>
                <h3 class="mt-4 font-extrabold text-brand-navy">Wallet management</h3>
                <p class="mt-2 text-sm leading-6 text-brand-muted">Top up once with MTN Mobile Money and use the same balance for every service.</p>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Fresh from the catalogue</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy">Latest active SMM products.</h2>
        </div>
        <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">Browse all SMM →</a>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        @foreach($brandStats as $brand)
            <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $brand['slug']]) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-brand-navy shadow-sm hover:border-brand-teal/60 hover:text-brand-teal">
                <img src="https://cdn.simpleicons.org/{{ $brand['slug'] }}" alt="{{ $brand['name'] }} logo" class="h-4 w-4" loading="lazy">
                {{ $brand['name'] }}
            </a>
        @endforeach
    </div>

    @if($featuredProducts->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-brand-muted">No active SMM products are published yet.</div>
    @else
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($featuredProducts as $product)
                <a href="{{ route('services.show', $product) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start justify-between gap-3">
                        <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-teal">
                            @if($product->category?->slug)<img src="https://cdn.simpleicons.org/{{ $product->category->slug }}" alt="{{ $product->category?->name }} logo" class="h-4 w-4" loading="lazy">@endif
                            <span>{{ $product->category?->name ?? 'SMM Service' }}</span>
                        </p>
                        <span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">Live</span>
                    </div>
                    <h3 class="mt-3 line-clamp-2 text-lg font-extrabold text-brand-navy">{{ $product->name }}</h3>
                    <p class="mt-2 line-clamp-3 text-sm text-brand-muted">{{ $product->summary }}</p>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                        <span class="text-sm font-bold text-brand-navy">{{ number_format((float) $product->price, $product->priceDecimals()) }}{{ $product->priceSuffix() }}</span>
                        <span class="text-xs font-semibold text-brand-muted">Min {{ $product->min_order ?? 1 }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

<section id="countries" class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Countries & coverage</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy sm:text-4xl">Worldwide SMM, verified virtual numbers.</h2>
            <p class="mt-3 text-brand-muted">SMM services are available worldwide across supported brands. Virtual-number coverage is expanding across countries, with live stock and pricing confirmed at checkout.</p>
        </div>
        <div class="mt-8 grid gap-5 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-brand-bg p-7">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">SMM services</p>
                <h3 class="mt-3 text-2xl font-extrabold text-brand-navy">Worldwide coverage</h3>
                <p class="mt-3 text-sm leading-6 text-brand-muted">Browse the catalogue for current products and pricing. Choose a platform, service group, option and quantity before checkout.</p>
                <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="mt-5 inline-flex text-sm font-bold text-brand-teal hover:text-brand-gold">Browse SMM services →</a>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-gold">Virtual numbers</p>
                <h3 class="mt-3 text-2xl font-extrabold text-brand-navy">Global SMS verification</h3>
                <p class="mt-3 text-sm leading-6 text-brand-muted">Open the catalogue to see current verification products, prices and delivery details before purchase.</p>
                <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="mt-5 inline-flex text-sm font-bold text-brand-teal hover:text-brand-gold">View verification services →</a>
            </div>
            <div class="rounded-3xl border border-dashed border-slate-300 bg-brand-bg p-7">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-muted">More countries</p>
                <h3 class="mt-3 text-2xl font-extrabold text-brand-navy">Full list after login</h3>
                <p class="mt-3 text-sm leading-6 text-brand-muted">The first 20 countries are shown below. Sign in to view the full country list and live availability.</p>
                <a href="#virtual-countries" class="mt-5 inline-flex text-sm font-bold text-brand-teal hover:text-brand-gold">See countries below ↓</a>
            </div>
        </div>
        <div id="virtual-countries" class="mt-8 rounded-3xl border border-slate-200 bg-brand-bg p-7">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">SMS verification countries</p>
            <p class="mt-2 max-w-2xl text-sm text-brand-muted">Live stock and pricing are confirmed at checkout. Sign in to view the full country list.</p>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @foreach($virtualCountries as $country)
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <img src="https://flagcdn.com/w40/{{ strtolower($country['code']) }}.png" alt="{{ $country['name'] }} flag" class="h-5 w-7 rounded object-cover ring-1 ring-slate-200" loading="lazy">
                        <span class="text-sm font-bold text-brand-navy">{{ $country['name'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 flex justify-end">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-teal hover:text-brand-gold">Login to view all countries <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="bg-brand-navy py-16 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">How it works</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">Three simple steps.</h2>
            <p class="mt-3 text-slate-300">Create your account, choose the right service and pay securely from your wallet.</p>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-7 backdrop-blur">
                <p class="text-sm font-extrabold text-brand-gold">Step 1</p>
                <h3 class="mt-3 text-xl font-extrabold">Create an account</h3>
                <p class="mt-3 text-sm leading-6 text-slate-300">Register with your email, verify your account and open your TrustVerifySMS wallet.</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-7 backdrop-blur">
                <p class="text-sm font-extrabold text-brand-teal">Step 2</p>
                <h3 class="mt-3 text-xl font-extrabold">Select your service</h3>
                <p class="mt-3 text-sm leading-6 text-slate-300">Choose SMM or verification, pick the platform/product, then set quantity or details.</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-7 backdrop-blur">
                <p class="text-sm font-extrabold text-brand-gold">Step 3</p>
                <h3 class="mt-3 text-xl font-extrabold">Enter details and pay</h3>
                <p class="mt-3 text-sm leading-6 text-slate-300">Add funds with MTN Mobile Money, confirm your order and track delivery in your dashboard.</p>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="max-w-3xl">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Payment methods</p>
        <h2 class="mt-2 text-3xl font-extrabold text-brand-navy sm:text-4xl">Simple ways to pay.</h2>
        <p class="mt-3 text-brand-muted">MTN Mobile Money is available now. Other payment methods are coming soon.</p>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <a href="{{ route('dashboard.wallet.index') }}" class="group relative overflow-hidden rounded-2xl bg-[#FFCB05] p-4 text-brand-navy shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-start justify-between gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/80 p-1.5"><img src="https://commons.wikimedia.org/wiki/Special:FilePath/MTN%20Logo.svg?width=200" alt="MTN logo" class="h-6 w-6" loading="lazy"></span>
                <span class="rounded-full bg-brand-navy px-2.5 py-0.5 text-[11px] font-bold text-white">Available</span>
            </div>
            <h3 class="mt-3 text-base font-extrabold">MTN Mobile Money</h3>
            <p class="mt-2 text-xs leading-5 text-brand-navy/80">Top up and pay with MTN MoMo.</p>
            <p class="mt-3 text-xs font-extrabold text-brand-navy">Top up wallet →</p>
        </a>

        <div class="rounded-2xl bg-[#FF7900] p-4 text-white shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 p-1.5"><img src="https://cdn.simpleicons.org/orange/ffffff" alt="Orange logo" class="h-5 w-5" loading="lazy"></span>
                <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-bold text-white">Coming soon</span>
            </div>
            <h3 class="mt-3 text-base font-extrabold">Orange Money</h3>
            <p class="mt-2 text-xs leading-5 text-white/85">Mobile money in preparation.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <span class="flex items-center gap-1.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F4F6FB] p-1.5"><img src="https://cdn.simpleicons.org/visa/1A1F71" alt="Visa logo" class="h-5 w-5" loading="lazy"></span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F4F6FB] p-1.5"><img src="https://cdn.simpleicons.org/mastercard" alt="Mastercard logo" class="h-5 w-5" loading="lazy"></span>
                </span>
                <span class="rounded-full bg-slate-200 px-2.5 py-0.5 text-[11px] font-bold text-brand-muted">Coming soon</span>
            </div>
            <h3 class="mt-3 text-base font-extrabold text-brand-navy">Visa / Mastercard</h3>
            <p class="mt-2 text-xs leading-5 text-brand-muted">Card payments not enabled yet.</p>
        </div>

        <div class="rounded-2xl bg-[#003087] p-4 text-white shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 p-1.5"><img src="https://cdn.simpleicons.org/paypal/ffffff" alt="PayPal logo" class="h-5 w-5" loading="lazy"></span>
                <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-bold text-white">Coming soon</span>
            </div>
            <h3 class="mt-3 text-base font-extrabold">PayPal</h3>
            <p class="mt-2 text-xs leading-5 text-white/85">PayPal deposits not enabled yet.</p>
        </div>

        <div class="rounded-2xl bg-brand-navy p-4 text-white shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10h18M7 15h4m4 0h4M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                </span>
                <span class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-bold text-white">Coming soon</span>
            </div>
            <h3 class="mt-3 text-base font-extrabold">Other methods</h3>
            <p class="mt-2 text-xs leading-5 text-white/85">Additional options planned.</p>
        </div>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl bg-brand-bg p-5"><h3 class="font-extrabold text-brand-navy">Wallet first</h3><p class="mt-1 text-sm text-brand-muted">Balances are checked before every order.</p></div>
        <div class="rounded-2xl bg-brand-bg p-5"><h3 class="font-extrabold text-brand-navy">Verified deposits</h3><p class="mt-1 text-sm text-brand-muted">Funds are credited after payment verification.</p></div>
        <div class="rounded-2xl bg-brand-bg p-5"><h3 class="font-extrabold text-brand-navy">Order history</h3><p class="mt-1 text-sm text-brand-muted">Payments, orders and delivery stay in your account.</p></div>
    </div>
</section>

<section class="relative overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy-light to-brand-navy px-4 py-16 text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl text-center">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Ready when you are</p>
        <h2 class="mt-3 text-3xl font-extrabold sm:text-5xl">Start growing with TrustVerifySMS today.</h2>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-300">Create an account, top up with MTN Mobile Money and place your first order in minutes.</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-teal px-7 py-4 text-sm font-extrabold text-brand-navy shadow-lg shadow-brand-teal/20 hover:bg-brand-gold">Register now</a>
            <a href="{{ route('services.index') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 px-7 py-4 text-sm font-extrabold text-white backdrop-blur hover:bg-white/10">Get started</a>
        </div>
    </div>
</section>

<section id="faq" class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-brand-teal/20 bg-brand-teal-soft p-8">
                <h2 class="text-2xl font-extrabold text-brand-navy">Clear rules beat fine print.</h2>
                <ul class="mt-5 space-y-3 text-sm text-brand-text">
                    <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Use services lawfully and follow each platform's rules.</li>
                    <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Review product price, minimum/maximum and terms before ordering.</li>
                    <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Keep your order ID ready for faster support.</li>
                    <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Never share passwords, API keys or payment details with support.</li>
                </ul>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-brand-bg p-8">
                <h2 class="text-2xl font-extrabold text-brand-navy">Frequently asked questions</h2>
                <div class="mt-5 space-y-4">
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">How do I order SMM services?</summary><p class="mt-2 text-sm text-brand-muted">Choose a platform, pick a service group, select an option, set quantity/link/details and pay from your wallet. Track the order from your dashboard.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">How are SMM prices shown?</summary><p class="mt-2 text-sm text-brand-muted">Each product page shows the price before checkout. Many services are priced per 1,000 and include minimum/maximum quantities.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">What if an SMM order is partial or delayed?</summary><p class="mt-2 text-sm text-brand-muted">Order status is visible in your dashboard. If delivery is partial or delayed, contact support with the order ID for review under the product terms.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">How do verification services work?</summary><p class="mt-2 text-sm text-brand-muted">Choose a supported verification product, use the provided number on the platform, then check the order for the incoming code.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">Which verification services are available?</summary><p class="mt-2 text-sm text-brand-muted">Verified US products are currently live for WhatsApp, Facebook, TikTok, Instagram and Snapchat SMS verification. Each product page shows price and availability.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">What if I do not receive an SMS code?</summary><p class="mt-2 text-sm text-brand-muted">Check the order status and expiry first. If no code arrives, open a support ticket with the order ID so we can review the supplier result.</p></details>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
