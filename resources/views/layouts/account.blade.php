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
                        @php($accountNotifications = auth()->user()->notifications()->latest()->limit(6)->get())
                        @php($accountUnread = auth()->user()->unreadNotifications()->count())
                        <details class="relative">
                            <summary class="relative flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-full border border-slate-200 text-brand-navy hover:bg-slate-100" aria-label="Notifications">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                @if($accountUnread > 0)
                                    <span class="absolute -right-1 -top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-brand-error px-1 text-[10px] font-extrabold text-white">{{ $accountUnread > 9 ? '9+' : $accountUnread }}</span>
                                @endif
                            </summary>
                            <div class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                                <div class="flex items-center justify-between px-2 py-1">
                                    <p class="text-sm font-extrabold text-brand-navy">Notifications</p>
                                    @if($accountUnread > 0)
                                        <form method="POST" action="{{ route('dashboard.notifications.read-all') }}">
                                            @csrf
                                            <button class="text-xs font-bold text-brand-teal hover:text-brand-gold">Mark all read</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="mt-1 max-h-80 overflow-y-auto">
                                    @forelse($accountNotifications as $notification)
                                        <a href="{{ route('dashboard.notifications.show', $notification) }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100 {{ $notification->read_at ? '' : 'bg-brand-teal-soft/60' }}">
                                            <span class="block text-sm font-bold text-brand-navy">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                            <span class="mt-0.5 block text-xs text-brand-muted">{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 120) }}</span>
                                            <span class="mt-1 block text-[11px] font-semibold text-brand-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                        </a>
                                    @empty
                                        <p class="px-3 py-4 text-sm text-brand-muted">No notifications yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </details>
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
                            <a href="{{ route('dashboard.wallet.index') }}#add-funds" class="rounded-lg bg-brand-teal px-3 py-2 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Deposit</a>
                        @endif

                        <details class="relative">
                            <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-full bg-brand-navy text-sm font-extrabold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</summary>
                            <div class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                                <p class="truncate px-3 py-2 text-sm font-bold text-brand-navy">{{ auth()->user()->name }}</p>
                                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">English</a>
                                <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Français</a>
                                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Website</a>
                                <a href="{{ route('profile') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">Profile</a>
                                <button type="button" data-theme-toggle class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-semibold hover:bg-slate-100">
                                    <span>Dark mode</span>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-navy text-white">
                                        <svg data-theme-icon="moon" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                                        <svg data-theme-icon="sun" class="hidden h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                                    </span>
                                </button>
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
            @if(session('error'))
                <div class="mb-5 rounded-xl border border-brand-error/20 bg-brand-error/10 px-4 py-3 text-sm font-semibold text-brand-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-xl border border-brand-error/20 bg-brand-error/10 px-4 py-3 text-sm font-semibold text-brand-error">{{ $errors->first() }}</div>
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
</body>
</html>
