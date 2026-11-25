<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="TrustVerifySMS Admin">
    <meta name="theme-color" content="#0A0F1E">
    <title>@yield('title', 'Admin') | TrustVerifySMS Admin</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-sans text-brand-text antialiased dark:bg-[#0B1220]">
<script>
(function () {
    var theme = localStorage.getItem('theme');
    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
})();
</script>

<div class="flex min-h-screen">
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col bg-brand-navy text-white lg:flex">
        <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-teal text-lg font-extrabold text-brand-navy">TV</div>
            <div>
                <p class="text-base font-extrabold leading-5">TrustVerifySMS</p>
                <p class="text-xs font-bold uppercase tracking-wider text-brand-teal">Admin console</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-4 text-sm font-bold" aria-label="Admin navigation">
            @php
                $adminLinks = [
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 13h2v6H3zM8 10h2v9H8zM13 5h2v14h-2zM18 8h2v11h-2z'],
                    ['route' => 'admin.orders.index', 'label' => 'Orders', 'icon' => 'M6 6h15l-1.5 9h-12L5 3H2M9 20a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM18 20a1.5 1.5 0 100-3 1.5 1.5 0 000 3z'],
                    ['route' => 'admin.products.index', 'label' => 'Products', 'icon' => 'M21 8.25l-9-5.25L3 8.25v7.5l9 5.25 9-5.25v-7.5zM3.3 8.8l9 5.2 9-5.2M12 21v-7'],
                    ['route' => 'admin.payments.index', 'label' => 'Payments', 'icon' => 'M3 6h18v13H3zM3 10h18M7 15h4'],
                    ['route' => 'admin.customers.index', 'label' => 'Customers', 'icon' => 'M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 20c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5'],
                    ['route' => 'admin.tickets.index', 'label' => 'Tickets', 'icon' => 'M8 10h8M8 14h5M21 12a8 8 0 01-11.6 7.1L4 20l1-4.4A8 8 0 1121 12z'],
                    ['route' => 'admin.contact-messages.index', 'label' => 'Messages', 'icon' => 'M4 6h16v12H4zM4 7l8 6 8-6'],
                    ['route' => 'admin.supplier-logs.index', 'label' => 'Supplier logs', 'icon' => 'M12 3l10 18H2L12 3zM12 10v5m0 3h.01'],
                    ['route' => 'admin.settings.index', 'label' => 'Settings', 'icon' => 'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.7 1.7 0 00.34 1.88l.06.06a2 2 0 11-2.83 2.83l-.06-.06A1.7 1.7 0 0015 19.4a1.7 1.7 0 00-1 1.55V21a2 2 0 11-4 0v-.09A1.7 1.7 0 009 19.4a1.7 1.7 0 00-1.88.34l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-1.55-1H3a2 2 0 110-4h.09A1.7 1.7 0 004.6 9a1.7 1.7 0 00-.34-1.88l-.06-.06a2 2 0 112.83-2.83l.06.06A1.7 1.7 0 009 4.6a1.7 1.7 0 001-1.55V3a2 2 0 114 0v.09c0 .68.4 1.3 1 1.55a1.7 1.7 0 001.88-.34l.06-.06a2 2 0 112.83 2.83l-.06.06A1.7 1.7 0 0019.4 9c.25.6.87 1 1.55 1H21a2 2 0 110 4h-.09c-.68 0-1.3.4-1.55 1z'],
                ];
            @endphp

            @foreach($adminLinks as $link)
                <a href="{{ route($link['route']) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs($link['route']) || request()->routeIs(str_replace('.index', '.*', $link['route'])) ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $link['icon'] }}"/></svg>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <a href="{{ route('home') }}" class="flex items-center justify-between rounded-xl bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                <span>View website</span>
                <span>→</span>
            </a>
        </div>
    </aside>

    <div class="flex min-h-screen flex-1 flex-col lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
            <div class="mx-auto flex h-16 max-w-[1400px] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-navy text-sm font-extrabold text-white">TV</div>
                    <p class="text-sm font-extrabold text-brand-navy dark:text-white">Admin</p>
                </div>

                <nav class="hidden flex-1 items-center gap-1 overflow-x-auto text-sm font-bold lg:flex" aria-label="Admin quick navigation">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">Dashboard</a>
                    <a href="{{ route('admin.orders.index') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('admin.orders.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">Orders</a>
                    <a href="{{ route('admin.products.index') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('admin.products.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">Products</a>
                    <a href="{{ route('admin.payments.index') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('admin.payments.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">Payments</a>
                    <a href="{{ route('admin.settings.index') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('admin.settings.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted hover:bg-slate-100 hover:text-brand-navy' }}">Settings</a>
                </nav>

                <div class="flex items-center gap-2">
                    <span class="hidden rounded-full bg-brand-teal-soft px-3 py-1 text-xs font-extrabold text-brand-navy sm:inline-flex">{{ auth()->user()->role }}</span>
                    <details class="relative">
                        <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-full bg-brand-navy text-sm font-extrabold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</summary>
                        <div class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                            <p class="truncate px-3 py-2 text-sm font-bold text-brand-navy dark:text-white">{{ auth()->user()->name }}</p>
                            <a href="{{ route('profile') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Profile</a>
                            <a href="{{ route('dashboard.overview') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Customer account</a>
                            <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Website</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="block w-full rounded-lg px-3 py-2 text-left text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Logout</button>
                            </form>
                        </div>
                    </details>
                </div>
            </div>

            <nav class="flex gap-1 overflow-x-auto border-t border-slate-100 px-4 py-2 text-xs font-extrabold lg:hidden dark:border-slate-800" aria-label="Admin mobile navigation">
                <a href="{{ route('admin.dashboard') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted' }}">Dashboard</a>
                <a href="{{ route('admin.orders.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('admin.orders.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted' }}">Orders</a>
                <a href="{{ route('admin.products.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('admin.products.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted' }}">Products</a>
                <a href="{{ route('admin.payments.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('admin.payments.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted' }}">Payments</a>
                <a href="{{ route('admin.tickets.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('admin.tickets.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted' }}">Tickets</a>
                <a href="{{ route('admin.settings.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 {{ request()->routeIs('admin.settings.*') ? 'bg-brand-teal-soft text-brand-navy' : 'text-brand-muted' }}">Settings</a>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-[1400px] flex-1 px-4 py-8 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-5 rounded-xl border border-brand-success/20 bg-brand-success/10 px-4 py-3 text-sm font-semibold text-brand-success">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-xl border border-brand-error/20 bg-brand-error/10 px-4 py-3 text-sm font-semibold text-brand-error">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@include('components.theme-toggle')
</body>
</html>
