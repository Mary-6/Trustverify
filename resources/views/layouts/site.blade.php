<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="TrustVerifySMS">
    <meta name="theme-color" content="#0A0F1E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TrustVerifySMS">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
    <title>@yield('title', 'TrustVerifySMS | SMM & Virtual Numbers')</title>
    <meta name="description" content="@yield('description', 'TrustVerifySMS is an international marketplace for verified SMM services and virtual numbers with secure wallet checkout.')">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="TrustVerifySMS | SMM & Virtual Numbers">
    <meta property="og:description" content="@yield('description', 'TrustVerifySMS is an international marketplace for verified SMM services and virtual numbers with secure wallet checkout.')">
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
<script>
(function () {
    var theme = localStorage.getItem('theme');
    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
})();
</script>
    <div class="min-h-screen flex flex-col">
        <div class="bg-brand-navy text-xs font-semibold text-slate-300">
            <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-2 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <span>Secure wallet checkout for SMM services and virtual numbers.</span>
                <span class="text-brand-teal">Price and availability shown before you pay.</span>
            </div>
        </div>
        <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="TrustVerifySMS home">
                    <img src="/images/logo-horizontal.svg" alt="TrustVerifySMS" class="h-9 w-auto">
                </a>
                <nav class="hidden items-center gap-2 text-sm font-semibold text-brand-muted lg:flex" aria-label="Primary">
                    <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 transition hover:bg-brand-teal-soft hover:text-brand-navy {{ request()->routeIs('home') ? 'bg-brand-teal-soft text-brand-navy' : '' }}">Home</a>
                    <a href="{{ route('about') }}" class="rounded-lg px-3 py-2 transition hover:bg-brand-teal-soft hover:text-brand-navy {{ request()->routeIs('about') ? 'bg-brand-teal-soft text-brand-navy' : '' }}">About</a>
                    <a href="{{ route('faq') }}" class="rounded-lg px-3 py-2 transition hover:bg-brand-teal-soft hover:text-brand-navy {{ request()->routeIs('faq') ? 'bg-brand-teal-soft text-brand-navy' : '' }}">FAQ</a>
                    <a href="{{ route('blog') }}" class="rounded-lg px-3 py-2 transition hover:bg-brand-teal-soft hover:text-brand-navy {{ request()->routeIs('blog') ? 'bg-brand-teal-soft text-brand-navy' : '' }}">Blogs</a>
                    <div class="group relative" data-nav-dropdown>
                        <button type="button" data-nav-dropdown-toggle aria-expanded="false" aria-haspopup="true" class="inline-flex items-center gap-1 rounded-lg px-3 py-2 transition hover:bg-brand-teal-soft hover:text-brand-navy {{ request()->routeIs('services.*') ? 'bg-brand-teal-soft text-brand-navy' : '' }}">
                            Services
                            <svg class="h-4 w-4 transition group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div data-nav-dropdown-menu class="absolute left-0 top-full z-50 mt-2 hidden w-72 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl ring-1 ring-black/5 transition-opacity group-hover:block group-focus-within:block">
                            <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-brand-teal-soft">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-teal-soft text-brand-navy">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                                </span>
                                <span>
                                    <span class="block text-sm font-extrabold text-brand-navy">SMM Services</span>
                                    <span class="block text-xs font-semibold text-brand-muted">Followers, likes, views and engagement</span>
                                </span>
                            </a>
                            <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-brand-teal-soft">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-teal-soft text-brand-navy">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                </span>
                                <span>
                                    <span class="block text-sm font-extrabold text-brand-navy">Verification Services</span>
                                    <span class="block text-xs font-semibold text-brand-muted">SMS verification numbers</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('contact') }}" class="rounded-lg px-3 py-2 transition hover:bg-brand-teal-soft hover:text-brand-navy {{ request()->routeIs('contact') ? 'bg-brand-teal-soft text-brand-navy' : '' }}">Contact</a>
                    @auth
                        <a href="{{ route('dashboard.overview') }}" class="rounded-lg bg-brand-navy px-4 py-2 text-sm font-bold text-white hover:bg-brand-navy-light">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg bg-brand-navy px-4 py-2 text-sm font-bold text-white hover:bg-brand-navy-light {{ request()->routeIs('login') ? 'ring-2 ring-brand-gold' : '' }}">Login</a>
                    @endauth
                </nav>
                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hidden rounded-lg px-3 py-2 text-sm font-bold text-brand-navy hover:bg-brand-teal-soft sm:inline-flex">Admin</a>
                        @endif
                    @endauth
                    <details class="relative lg:hidden">
                        <summary class="cursor-pointer list-none rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-brand-navy">Menu</summary>
                        <div class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
                            <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Home</a>
                            <a href="{{ route('about') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">About</a>
                            <a href="{{ route('faq') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">FAQ</a>
                            <a href="{{ route('blog') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Blogs</a>
                            <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">SMM Services</a>
                            <a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Verification Services</a>
                            <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Contact</a>
                            @auth
                                <a href="{{ route('dashboard.overview') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Login</a>
                                <a href="{{ route('register') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Create account</a>
                            @endauth
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
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-6 lg:px-8">
                <div class="lg:col-span-2">
                    <img src="/images/logo-horizontal-light.svg" alt="TrustVerifySMS" class="h-10 w-auto">
                    <p class="mt-4 max-w-sm text-sm leading-6">A secure marketplace for verified SMM services and virtual numbers with wallet checkout and order tracking.</p>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">Encrypted checkout, wallet transactions, supplier logs and role-based administration.</p>
                </div>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Services</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="hover:text-brand-teal">SMM Services</a></li>
                        <li><a href="{{ route('services.index', ['service' => 'virtual_number']) }}" class="hover:text-brand-teal">Verification Services</a></li>
                        <li><a href="{{ url('/').'#countries' }}" class="hover:text-brand-teal">Countries</a></li>
                        <li><a href="{{ url('/').'#how-it-works' }}" class="hover:text-brand-teal">How it works</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Company</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('about') }}" class="hover:text-brand-teal">About</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-brand-teal">FAQ</a></li>
                        <li><a href="{{ route('blog') }}" class="hover:text-brand-teal">Blog</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-brand-teal">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Legal</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('terms') }}" class="hover:text-brand-teal">Terms of Service</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-brand-teal">Privacy Policy</a></li>
                        <li><a href="{{ route('refund') }}" class="hover:text-brand-teal">Refund Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Support</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('support') }}" class="hover:text-brand-teal">Support centre</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-brand-teal">Help & FAQ</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-brand-teal">Create account</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <span>&copy; {{ date('Y') }} TrustVerifySMS. All rights reserved.</span>
                    <span>SMM and virtual numbers marketplace.</span>
                </div>
            </div>
        </footer>
    </div>
    @include('components.theme-toggle')
</body>
</html>
