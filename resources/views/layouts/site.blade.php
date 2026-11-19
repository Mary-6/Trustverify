<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="Trustverifysms">
    <meta name="theme-color" content="#101A2C">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Trustverifysms">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
    <title>@yield('title', 'Trustverifysms | SMM & Virtual Numbers')</title>
    <meta name="description" content="@yield('description', 'Trustverifysms is an international marketplace for verified SMM services and virtual numbers with secure wallet checkout.')">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="Trustverifysms | SMM & Virtual Numbers">
    <meta property="og:description" content="@yield('description', 'Trustverifysms is an international marketplace for verified SMM services and virtual numbers with secure wallet checkout.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    @hasSection('meta')@yield('meta')@endif
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-bg font-sans text-brand-text antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Trustverifysms home">
                    <img src="/images/logo-horizontal.svg" alt="Trustverifysms" class="h-9 w-auto">
                </a>
                <nav class="hidden items-center gap-6 text-sm font-semibold text-brand-muted lg:flex" aria-label="Primary">
                    <details class="relative">
                        <summary class="cursor-pointer list-none hover:text-brand-teal [&::-webkit-details-marker]:hidden">Services</summary>
                        <div class="absolute left-0 top-full z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">SMM Services</a>
                            <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Virtual Numbers</a>
                        </div>
                    </details>
                    <a href="{{ route('pricing') }}" class="hover:text-brand-teal">Pricing</a>
                    <a href="{{ route('support') }}" class="hover:text-brand-teal">Support</a>
                </nav>
                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hidden rounded-lg px-3 py-2 text-sm font-bold text-brand-navy hover:bg-brand-teal-soft sm:inline-flex">Admin</a>
                        @endif
                        <a href="{{ route('dashboard.overview') }}" class="rounded-lg bg-brand-navy px-4 py-2 text-sm font-bold text-white hover:bg-brand-navy-light">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-lg px-3 py-2 text-sm font-bold text-brand-navy hover:bg-slate-100 sm:inline-flex">Sign in</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-brand-teal px-4 py-2 text-sm font-bold text-brand-navy hover:bg-brand-gold">Create account</a>
                    @endauth
                    <details class="relative lg:hidden">
                        <summary class="cursor-pointer list-none rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-brand-navy">Menu</summary>
                        <div class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
                            <a href="{{ route('services.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Services</a>
                            <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">SMM Services</a>
                            <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Virtual Numbers</a>
                            <a href="{{ route('pricing') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Pricing</a>
                            <a href="{{ route('support') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Support</a>
                            @guest
                                <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Sign in</a>
                                <a href="{{ route('register') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Create account</a>
                            @endguest
                        </div>
                    </details>
                </div>
            </div>
        </header>

        <main class="flex-1">
            @if(session('status'))
                <div class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-xl border border-brand-success/20 bg-brand-success/10 px-4 py-3 text-sm font-semibold text-brand-success">{{ session('status') }}</div>
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="bg-brand-navy text-slate-300">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-5 lg:px-8">
                <div class="lg:col-span-2">
                    <img src="/images/logo-horizontal-light.svg" alt="Trustverifysms" class="h-10 w-auto">
                    <p class="mt-4 max-w-sm text-sm leading-6">A secure marketplace for verified SMM services and virtual numbers with wallet checkout and order tracking.</p>
                </div>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Services</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="hover:text-brand-teal">SMM Services</a></li>
                        <li><a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="hover:text-brand-teal">Virtual Numbers</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Company</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('pricing') }}" class="hover:text-brand-teal">Pricing</a></li>
                        <li><a href="{{ route('api.docs') }}" class="hover:text-brand-teal">Developer API</a></li>
                        <li><a href="{{ route('support') }}" class="hover:text-brand-teal">Support</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-brand-teal">Customer login</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Security</h3>
                    <p class="mt-4 text-sm leading-6">Encrypted checkout, wallet transactions, supplier logs and role-based administration.</p>
                </div>
            </div>
            <div class="border-t border-white/10">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <span>&copy; {{ date('Y') }} Trustverifysms. All rights reserved.</span>
                    <span>SMM and virtual numbers marketplace.</span>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
