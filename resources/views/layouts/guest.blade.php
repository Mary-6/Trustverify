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
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">

        <title>{{ config('app.name', 'TrustVerifySMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
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
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-brand-bg">
            <div>
                <a href="/" wire:navigate>
                    <img src="/images/logo-horizontal.svg" alt="TrustVerifySMS" class="h-12 w-auto">
                </a>
            </div>

            <div class="mt-4 flex gap-3 text-sm font-bold">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="rounded-lg px-3 py-1 {{ app()->getLocale() === 'en' ? 'bg-brand-navy text-white' : 'text-brand-muted hover:text-brand-navy' }}">EN</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="rounded-lg px-3 py-1 {{ app()->getLocale() === 'fr' ? 'bg-brand-navy text-white' : 'text-brand-muted hover:text-brand-navy' }}">FR</a>
            </div>
            @include('components.theme-toggle')

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
