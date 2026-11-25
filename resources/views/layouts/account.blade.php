<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="TrustVerifySMS">
    <meta name="theme-color" content="#071C1A">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TrustVerifySMS">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
    <title>@yield('title', 'Account') | TrustVerifySMS</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-bg font-sans text-brand-text antialiased">
<script>
(function () {
    var theme = localStorage.getItem('theme');
    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
})();
</script>
    @auth
        @unless(auth()->user()->isAdmin())
            @php($accountWallet = app(\App\Services\WalletService::class)->walletFor(auth()->user()))
        @endunless
    @endauth

    <div class="min-h-screen">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="TrustVerifySMS home">
                    <img src="/images/logo-horizontal.svg" alt="TrustVerifySMS" class="h-9 w-auto">
                </a>

                <nav class="flex min-w-0 flex-1 items-center gap-1 overflow-x-auto text-sm font-bold" aria-label="Account navigation">
                    <a href="{{ route('services.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('services.*') || request()->routeIs('dashboard.orders.create') || request()->routeIs('dashboard.verification.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">Order</a>
                    <a href="{{ route('dashboard.orders.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('dashboard.orders.index') || request()->routeIs('dashboard.orders.show') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">My Orders</a>
                    <a href="{{ route('dashboard.wallet.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('dashboard.wallet.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">Wallet</a>
                    <a href="{{ route('dashboard.overview') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('dashboard.overview') || request()->routeIs('dashboard.numbers.*') || request()->routeIs('profile') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">More</a>
                </nav>

                <div class="flex shrink-0 items-center gap-2">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <details class="relative">
                                <summary class="cursor-pointer list-none rounded-lg bg-brand-navy px-3 py-2 text-sm font-bold text-white">Admin</summary>
                                <div class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                                    <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Admin overview</a>
                                    <a href="{{ route('admin.orders.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Admin orders</a>
                                    <a href="{{ route('admin.products.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Products</a>
                                    <a href="{{ route('admin.categories.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Categories</a>
                                    <a href="{{ route('admin.countries.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Countries</a>
                                    <a href="{{ route('admin.payments.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Payments</a>
                                    <a href="{{ route('admin.customers.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Customers</a>
                                    <a href="{{ route('admin.tickets.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Tickets</a>
                                    <a href="{{ route('admin.contact-messages.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Messages</a>
                                    <a href="{{ route('admin.supplier-logs.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Supplier logs</a>
                                    <a href="{{ route('admin.settings.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Settings</a>
                                </div>
                            </details>
                        @else
                            <div class="hidden items-center gap-2 rounded-xl bg-brand-bg px-3 py-2 text-sm sm:flex">
                                <span class="text-xs font-bold uppercase text-brand-muted">Balance</span>
                                <span class="font-extrabold text-brand-navy">{{ number_format((float) $accountWallet->balance, 0) }} {{ $accountWallet->currency }}</span>
                            </div>
                            <a href="{{ route('dashboard.wallet.index') }}#add-funds" class="rounded-lg bg-brand-teal px-3 py-2 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Add Money</a>
                        @endif

                        <details class="relative">
                            <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-full bg-brand-navy text-sm font-extrabold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</summary>
                            <div class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                                <p class="truncate px-3 py-2 text-sm font-bold text-brand-navy">{{ auth()->user()->name }}</p>
                                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">English</a>
                                <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Français</a>
                                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Website</a>
                                <a href="{{ route('profile') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="block w-full rounded-lg px-3 py-2 text-left text-sm font-semibold hover:bg-slate-100">Logout</button>
                                </form>
                            </div>
                        </details>
                    @endauth
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-5 rounded-xl border border-brand-success/20 bg-brand-success/10 px-4 py-3 text-sm font-semibold text-brand-success">{{ session('status') }}</div>
            @endif
            @if(session('api_token'))
                <div class="mb-5 rounded-xl border border-brand-gold/40 bg-brand-gold/10 p-4">
                    <p class="text-sm font-bold text-brand-navy">Copy your API token now</p>
                    <code class="mt-2 block break-all rounded-lg bg-white p-3 text-xs text-brand-text">{{ session('api_token') }}</code>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    @include('components.theme-toggle')
</body>
</html>
