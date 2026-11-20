@extends('layouts.site')

@section('title', 'TrustVerifySMS | SMM & Virtual Numbers')
@section('description', 'Buy worldwide SMM services — followers, likes, shares, subscribers and page followers — plus virtual numbers for SMS verification, with secure wallet checkout.')

@section('content')
<section class="relative overflow-hidden bg-brand-navy text-white">
    <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 18% 18%, #06B6D4 0, transparent 34%), radial-gradient(circle at 82% 12%, #F59E0B 0, transparent 26%), radial-gradient(circle at 60% 85%, #06B6D4 0, transparent 28%);"></div>
    <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-brand-bg to-transparent"></div>
    <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8 lg:pb-28 lg:pt-24">
        <div class="max-w-3xl">
            <p class="inline-flex rounded-full border border-brand-teal/40 bg-brand-teal/10 px-4 py-1 text-sm font-bold text-brand-teal">Live SMM catalogue · virtual numbers · one wallet</p>
            <h1 class="mt-6 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">SMM services and virtual numbers, in one wallet.</h1>
            <p class="mt-6 text-lg leading-8 text-slate-300">Grow Instagram, TikTok, Facebook, YouTube, Telegram and WhatsApp with verified SMM services, or get virtual numbers for SMS verification — with price and availability shown before you pay.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy shadow-lg shadow-brand-teal/20 hover:bg-brand-gold">Explore SMM services</a>
                <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 px-6 py-3 text-sm font-extrabold text-white backdrop-blur hover:bg-white/10">Virtual numbers</a>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"><p class="text-2xl font-extrabold text-brand-teal">{{ number_format($stats['smm_products']) }}</p><p class="text-sm text-slate-300">Active SMM products</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"><p class="text-2xl font-extrabold text-brand-teal">{{ number_format($stats['brands']) }}</p><p class="text-sm text-slate-300">Brands covered</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"><p class="text-2xl font-extrabold text-brand-teal">{{ number_format($stats['virtual_numbers']) }}</p><p class="text-sm text-slate-300">Virtual numbers live</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"><p class="text-2xl font-extrabold text-brand-gold">Wallet</p><p class="text-sm text-slate-300">Secure checkout</p></div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Two ways to grow</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy">One account, one balance, clear prices.</h2>
            <p class="mt-2 max-w-2xl text-brand-muted">Start with SMM growth or SMS verification. Every order uses the same wallet and order history.</p>
        </div>
        <a href="{{ route('services.index') }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">View all services →</a>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-teal to-brand-gold"></div>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">SMM Services</p>
                    <h3 class="mt-2 text-2xl font-extrabold text-brand-navy">Followers, likes, shares, subscribers and more.</h3>
                </div>
                <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-xs font-bold text-brand-navy">Live</span>
            </div>
            <p class="mt-4 text-sm leading-6 text-brand-muted">Choose a platform, pick a service group, compare verified options and check the price before checkout.</p>
            <div class="mt-6 grid grid-cols-3 gap-3 text-sm">
                <div class="rounded-2xl bg-brand-bg p-3"><p class="font-extrabold text-brand-navy">{{ number_format($stats['smm_products']) }}</p><p class="text-xs text-brand-muted">Products</p></div>
                <div class="rounded-2xl bg-brand-bg p-3"><p class="font-extrabold text-brand-navy">{{ number_format($stats['brands']) }}</p><p class="text-xs text-brand-muted">Brands</p></div>
                <div class="rounded-2xl bg-brand-bg p-3"><p class="font-extrabold text-brand-navy">Price</p><p class="text-xs text-brand-muted">Before checkout</p></div>
            </div>
            <p class="mt-6 text-sm font-bold text-brand-teal">Explore SMM →</p>
        </a>

        <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-gold to-brand-teal"></div>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-brand-gold">Virtual Numbers</p>
                    <h3 class="mt-2 text-2xl font-extrabold text-brand-navy">Private numbers for SMS verification.</h3>
                </div>
                <span class="rounded-full {{ $stats['virtual_numbers'] > 0 ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted' }} px-3 py-1 text-xs font-bold">{{ $stats['virtual_numbers'] > 0 ? 'Live' : 'Soon' }}</span>
            </div>
            <p class="mt-4 text-sm leading-6 text-brand-muted">Receive verification codes for supported services, with product details and pricing shown before purchase.</p>
            <div class="mt-6 grid grid-cols-3 gap-3 text-sm">
                <div class="rounded-2xl bg-brand-bg p-3"><p class="font-extrabold text-brand-navy">{{ number_format($stats['virtual_numbers']) }}</p><p class="text-xs text-brand-muted">Products</p></div>
                <div class="rounded-2xl bg-brand-bg p-3"><p class="font-extrabold text-brand-navy">SMS</p><p class="text-xs text-brand-muted">Codes</p></div>
                <div class="rounded-2xl bg-brand-bg p-3"><p class="font-extrabold text-brand-navy">Wallet</p><p class="text-xs text-brand-muted">One balance</p></div>
            </div>
            <p class="mt-6 text-sm font-bold text-brand-teal">View virtual numbers →</p>
        </a>
    </div>
</section>

<section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Live catalogue</p>
                <h2 class="mt-2 text-3xl font-extrabold text-brand-navy">Pick a brand, then choose your service.</h2>
                <p class="mt-2 max-w-2xl text-brand-muted">Product counts update from the active catalogue. Choose a brand to see followers, likes, shares, subscribers and related services.</p>
            </div>
            <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">Browse all SMM →</a>
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($brandStats as $brand)
                <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $brand['slug']]) }}" class="group rounded-2xl border border-slate-200 bg-brand-bg p-5 transition hover:-translate-y-1 hover:border-brand-teal/50 hover:bg-white hover:shadow-xl">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-lg font-extrabold text-brand-navy group-hover:text-brand-teal">{{ $brand['name'] }}</h3>
                        <span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">Live</span>
                    </div>
                    <p class="mt-1 text-sm text-brand-muted">{{ number_format($brand['products_count']) }} services available</p>
                    <p class="mt-4 text-sm font-bold text-brand-teal">Choose service →</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section id="countries" class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Countries & coverage</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy">Worldwide SMM, verified virtual numbers.</h2>
            <p class="mt-3 text-brand-muted">SMM services are available worldwide across supported brands. Virtual-number coverage is expanding across countries, with live stock and pricing confirmed at checkout.</p>
        </div>
        <div class="mt-8 grid gap-5 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-brand-bg p-7">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">SMM services</p>
                <h3 class="mt-3 text-2xl font-extrabold text-brand-navy">Worldwide coverage</h3>
                <p class="mt-3 text-sm leading-6 text-brand-muted">{{ number_format($stats['smm_products']) }} active products across {{ number_format($stats['brands']) }} brands. Choose a platform, service group, option and quantity before checkout.</p>
                <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="mt-5 inline-flex text-sm font-bold text-brand-teal hover:text-brand-gold">Browse SMM services →</a>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-gold">Virtual numbers</p>
                <h3 class="mt-3 text-2xl font-extrabold text-brand-navy">Global SMS verification</h3>
                <p class="mt-3 text-sm leading-6 text-brand-muted">{{ number_format($stats['virtual_numbers']) }} products are live today, with country coverage expanding across supported markets. Sign in to view live availability, prices and delivery details.</p>
                <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="mt-5 inline-flex text-sm font-bold text-brand-teal hover:text-brand-gold">View virtual numbers →</a>
            </div>
            <div class="rounded-3xl border border-dashed border-slate-300 bg-brand-bg p-7">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-muted">More countries</p>
                <h3 class="mt-3 text-2xl font-extrabold text-brand-navy">Full list after login</h3>
                <p class="mt-3 text-sm leading-6 text-brand-muted">The first 20 countries are shown below. Sign in to view the full country list and live availability.</p>
                <a href="#virtual-countries" class="mt-5 inline-flex text-sm font-bold text-brand-teal hover:text-brand-gold">See countries below ↓</a>
            </div>
        </div>
        <div id="virtual-countries" class="mt-8 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">SMS verification countries</p>
            <p class="mt-2 max-w-2xl text-sm text-brand-muted">Live stock and pricing are confirmed at checkout. Sign in to view the full country list.</p>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @foreach($virtualCountries as $country)
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-brand-bg px-4 py-3">
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

<section id="how-it-works" class="bg-brand-navy py-16 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">How it works</p>
            <h2 class="mt-2 text-3xl font-extrabold">Three steps, all on screen.</h2>
            <p class="mt-3 text-slate-300">No hidden queue. Create your account, fund your wallet, pick a service and track every order from your dashboard.</p>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur"><p class="text-sm font-bold text-brand-gold">Step 1</p><h3 class="mt-2 text-xl font-extrabold">Top up once</h3><p class="mt-2 text-sm leading-6 text-slate-300">Create an account and add funds to your wallet using the payment options shown at checkout.</p></div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur"><p class="text-sm font-bold text-brand-teal">Step 2</p><h3 class="mt-2 text-xl font-extrabold">Pick a service</h3><p class="mt-2 text-sm leading-6 text-slate-300">Choose SMM or virtual numbers, select a brand/service/option, set quantity and review the price.</p></div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur"><p class="text-sm font-bold text-brand-gold">Step 3</p><h3 class="mt-2 text-xl font-extrabold">Watch it arrive</h3><p class="mt-2 text-sm leading-6 text-slate-300">Track order status, delivery details and support tickets from your dashboard.</p></div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-6 lg:grid-cols-[1fr_420px]">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Payments</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-navy">Pay from your wallet.</h2>
            <p class="mt-3 max-w-2xl text-brand-muted">Add funds once, then use the same balance for SMM services and virtual numbers. Payment methods and verification times are shown in your wallet before you submit a deposit.</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-brand-bg p-4"><h3 class="font-bold text-brand-navy">Wallet first</h3><p class="mt-1 text-sm text-brand-muted">Balances are checked before every order.</p></div>
                <div class="rounded-2xl bg-brand-bg p-4"><h3 class="font-bold text-brand-navy">Verified deposits</h3><p class="mt-1 text-sm text-brand-muted">Funds are credited after payment verification.</p></div>
                <div class="rounded-2xl bg-brand-bg p-4"><h3 class="font-bold text-brand-navy">Order history</h3><p class="mt-1 text-sm text-brand-muted">Payments, orders and delivery stay in your account.</p></div>
            </div>
        </div>
        <div class="rounded-3xl bg-brand-teal-soft p-8">
            <h2 class="text-2xl font-extrabold text-brand-navy">Put orders inside your own product.</h2>
            <p class="mt-3 text-sm leading-7 text-brand-text">Use API keys to read products, place orders and check wallet/order status from your own code. Keys are hashed, revocable and scoped from your account.</p>
            <a href="{{ route('api.docs') }}" class="mt-6 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Read API docs</a>
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
                    <li class="flex gap-3"><span class="font-extrabold text-brand-teal">✓</span> Never share passwords, API keys or payment card details with support.</li>
                </ul>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-brand-bg p-8">
                <h2 class="text-2xl font-extrabold text-brand-navy">Frequently asked questions</h2>
                <div class="mt-5 space-y-4">
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">How do I order SMM services?</summary><p class="mt-2 text-sm text-brand-muted">Choose a platform, pick a service group, select an option, set quantity/link/details and pay from your wallet. Track the order from your dashboard.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">How are SMM prices shown?</summary><p class="mt-2 text-sm text-brand-muted">Each product page shows the price before checkout. Many services are priced per 1,000 and include minimum/maximum quantities.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">What if an SMM order is partial or delayed?</summary><p class="mt-2 text-sm text-brand-muted">Order status is visible in your dashboard. If delivery is partial or delayed, contact support with the order ID for review under the product terms.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">How do virtual numbers work?</summary><p class="mt-2 text-sm text-brand-muted">Choose a supported SMS-verification product, use the provided number on the platform, then check the order for the incoming code.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">Which virtual number services are available?</summary><p class="mt-2 text-sm text-brand-muted">Verified US products are currently live for WhatsApp, Facebook, TikTok, Instagram and Snapchat SMS verification. Each product page shows price and availability.</p></details>
                    <details class="rounded-2xl border border-slate-200 bg-white p-4"><summary class="cursor-pointer font-bold text-brand-navy">What if I do not receive an SMS code?</summary><p class="mt-2 text-sm text-brand-muted">Check the order status and expiry first. If no code arrives, open a support ticket with the order ID so we can review the supplier result.</p></details>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
