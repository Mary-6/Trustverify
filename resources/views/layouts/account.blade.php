<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="TrustVerifySMS">
    <meta name="theme-color" content="#101A2C">
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
    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="/images/logo-horizontal.svg" alt="TrustVerifySMS" class="h-9 w-auto">
                </a>
                <div class="flex items-center gap-3 text-sm font-bold">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-brand-navy hover:bg-brand-teal-soft">Admin</a>
                    @endif
                    <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-brand-muted hover:bg-slate-100">Website</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg bg-brand-navy px-3 py-2 text-white hover:bg-brand-navy-light">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[260px_1fr] lg:px-8">
            <aside>
                <nav class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm" aria-label="Account navigation">
                    @if(auth()->user()->isAdmin())
                        <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-brand-muted">Administration</p>
                        <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.dashboard') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Overview</a>
                        <a href="{{ route('admin.orders.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.orders.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Orders</a>
                        <a href="{{ route('admin.products.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.products.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Products</a>
                        <a href="{{ route('admin.categories.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.categories.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Categories</a>
                        <a href="{{ route('admin.countries.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.countries.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Countries</a>
                        <a href="{{ route('admin.payments.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.payments.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Payments</a>
                        <a href="{{ route('admin.customers.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.customers.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Customers</a>
                        <a href="{{ route('admin.tickets.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.tickets.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Tickets</a>
                        <a href="{{ route('admin.supplier-logs.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.supplier-logs.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Supplier logs</a>
                        <a href="{{ route('admin.settings.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('admin.settings.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Settings</a>
                    @else
                        <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-brand-muted">Customer</p>
                        <a href="{{ route('dashboard.overview') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('dashboard.overview') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Overview</a>
                        <a href="{{ route('services.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('services.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Buy services</a>
                        <a href="{{ route('dashboard.orders.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('dashboard.orders.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Orders</a>
                        <a href="{{ route('dashboard.numbers.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('dashboard.numbers.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Virtual numbers</a>
                        <a href="{{ route('dashboard.wallet.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('dashboard.wallet.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Wallet</a>
                        <a href="{{ route('dashboard.api-keys.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('dashboard.api-keys.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">API access</a>
                        <a href="{{ route('dashboard.tickets.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-brand-teal-soft {{ request()->routeIs('dashboard.tickets.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-text' }}">Support tickets</a>
                    @endif
                </nav>
            </aside>

            <main class="min-w-0">
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
    </div>
</body>
</html>
