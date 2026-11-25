@extends('layouts.site')

@php
    $apps = $catalog['apps'] ?? [];
@endphp

@section('title', 'Verification Services | TrustVerifySMS')
@section('description', 'Choose an app and country to receive a real SMS verification number. Prices are shown in FCFA before purchase.')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="max-w-3xl">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Verification Services</p>
        <h1 class="mt-2 text-4xl font-extrabold text-brand-navy">Receive SMS verification codes.</h1>
        <p class="mt-3 text-brand-muted">Choose an app, pick a supported country, get a real number and receive the verification SMS in your account. Only apps with active verification products are shown.</p>
    </div>

    <div class="mt-8 grid gap-3 sm:grid-cols-4" id="verification-progress">
        <div class="verification-step rounded-2xl border border-brand-teal bg-brand-teal-soft px-4 py-3 text-sm font-extrabold text-brand-navy" data-step="1">1 · Choose an app</div>
        <div class="verification-step rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-brand-muted" data-step="2">2 · Choose a country</div>
        <div class="verification-step rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-brand-muted" data-step="3">3 · Get your number</div>
        <div class="verification-step rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-brand-muted" data-step="4">4 · Receive code</div>
    </div>

    <div class="mt-8">
        <div id="verification-apps">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">STEP 1 – Choose an app</p>
            @if(empty($apps))
                <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-brand-muted">No verification apps are available right now.</div>
            @else
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach($apps as $app)
                        <button type="button" class="verification-app group flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-teal/60 hover:shadow-md" data-app="{{ $app['slug'] }}" title="{{ $app['label'] }} · {{ $app['country_count'] }} {{ $app['country_count'] === 1 ? 'country' : 'countries' }} · {{ number_format($app['option_count']) }} options" aria-label="Choose {{ $app['label'] }} for verification">
                            <img src="https://cdn.simpleicons.org/{{ $app['logo'] }}/{{ ltrim($app['color'] ?? '#0F172A', '#') }}" alt="{{ $app['label'] }} logo" class="h-9 w-9" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div id="verification-country-step" class="mt-8 hidden">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <button type="button" id="verification-back-app" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← Choose another app</button>
                    <p class="mt-2 text-sm font-bold uppercase tracking-wider text-brand-teal">STEP 2 – Choose a country</p>
                    <h2 class="mt-1 text-2xl font-extrabold text-brand-navy">Select a country for <span id="verification-selected-app">this app</span></h2>
                </div>
            </div>
            <input type="text" id="verification-country-search" placeholder="Search countries..." class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal sm:max-w-md">
            <div id="verification-countries" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"></div>
        </div>

        <div id="verification-option-step" class="mt-8 hidden">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <button type="button" id="verification-back-country" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← Choose another country</button>
                    <p class="mt-2 text-sm font-bold uppercase tracking-wider text-brand-teal">STEP 3 – Get your number</p>
                    <h2 class="mt-1 text-2xl font-extrabold text-brand-navy">Choose a number option</h2>
                    <p class="mt-1 text-sm text-brand-muted" id="verification-selected-country"></p>
                </div>
            </div>
            <div id="verification-options" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"></div>
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 text-sm text-brand-muted">
                <span class="font-extrabold text-brand-navy">Step 4</span> happens after purchase: your number, SMS status and verification code appear in your account with automatic provider checks.
            </div>
        </div>
    </div>
</section>

<script>
window.verificationCatalog = @js($catalog);
window.verificationIsAuth = @js((bool) $wallet);
window.verificationWallet = @js($wallet ? ['balance' => (float) $wallet->balance, 'currency' => $wallet->currency] : null);
window.verificationPurchaseUrl = @js(route('dashboard.verification.orders.store'));
window.verificationLoginUrl = @js(route('login'));
</script>
@push('scripts')
<script>
(function () {
    const catalog = window.verificationCatalog || { apps: [] };
    const isAuth = window.verificationIsAuth;
    const purchaseUrl = window.verificationPurchaseUrl;
    const loginUrl = window.verificationLoginUrl;
    const state = { app: null, country: null };

    const appsEl = document.getElementById('verification-apps');
    const countryStep = document.getElementById('verification-country-step');
    const optionStep = document.getElementById('verification-option-step');
    const countriesEl = document.getElementById('verification-countries');
    const optionsEl = document.getElementById('verification-options');
    const searchEl = document.getElementById('verification-country-search');

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char];
        });
    }

    function money(amount, currency, decimals) {
        return Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: decimals || 0, maximumFractionDigits: decimals || 0 }) + ' ' + (currency || 'XAF');
    }

    function setProgress(step) {
        document.querySelectorAll('.verification-step').forEach(function (el) {
            const current = Number(el.dataset.step);
            el.className = 'verification-step rounded-2xl border px-4 py-3 text-sm font-bold ' + (current === step ? 'border-brand-teal bg-brand-teal-soft text-brand-navy' : current < step ? 'border-brand-teal/40 bg-white text-brand-navy' : 'border-slate-200 bg-white text-brand-muted');
        });
    }

    function selectedApp() {
        return catalog.apps.find(function (app) { return app.slug === state.app; }) || null;
    }

    function renderCountries(filter) {
        const app = selectedApp();
        countriesEl.innerHTML = '';
        if (!app) return;
        const query = (filter || '').trim().toLowerCase();
        const countries = app.countries.filter(function (country) {
            return !query || country.name.toLowerCase().includes(query) || country.code.toLowerCase().includes(query);
        });
        if (!countries.length) {
            countriesEl.innerHTML = '<div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-brand-muted sm:col-span-2 lg:col-span-3 xl:col-span-4">No countries match your search.</div>';
            return;
        }
        countries.forEach(function (country) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'verification-country flex items-center gap-4 rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-xl ' + (state.country === country.code ? 'border-brand-teal ring-2 ring-brand-teal/30' : 'border-slate-200');
            button.dataset.country = country.code;
            button.innerHTML = '<img src="' + esc(country.flag) + '" alt="' + esc(country.name) + ' flag" class="h-8 w-12 rounded object-cover" loading="lazy"><span class="min-w-0"><span class="block truncate text-base font-extrabold text-brand-navy">' + esc(country.name) + '</span><span class="block text-xs font-bold uppercase text-brand-muted">' + esc(country.code) + ' · ' + country.options.length + ' option' + (country.options.length === 1 ? '' : 's') + '</span></span>';
            button.addEventListener('click', function () { chooseCountry(country.code); });
            countriesEl.appendChild(button);
        });
    }

    function chooseCountry(code) {
        state.country = code;
        renderCountries(searchEl.value);
        renderOptions();
        optionStep.classList.remove('hidden');
        setProgress(3);
        optionStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function renderOptions() {
        const app = selectedApp();
        const country = app ? app.countries.find(function (item) { return item.code === state.country; }) : null;
        optionsEl.innerHTML = '';
        document.getElementById('verification-selected-country').textContent = country ? country.name + ' (' + country.code + ')' : '';
        if (!country) return;

        country.options.forEach(function (option) {
            const card = document.createElement('div');
            card.className = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
            const available = option.available;
            const badge = available ? '<span class="rounded-full bg-brand-teal-soft px-2 py-1 text-xs font-bold text-brand-navy">Available</span>' : '<span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-brand-muted">' + (option.provider_ready ? 'Unavailable' : 'Provider offline') + '</span>';
            let action = '';
            if (!isAuth) {
                action = '<a href="' + esc(loginUrl) + '" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-brand-navy px-4 py-3 text-sm font-extrabold text-white hover:bg-brand-navy-light">Login to get number</a>';
            } else if (available) {
                action = '<form method="POST" action="' + esc(purchaseUrl) + '" class="mt-5"><input type="hidden" name="_token" value="' + esc(document.querySelector('meta[name=csrf-token]')?.content || '') + '"><input type="hidden" name="product_id" value="' + option.id + '"><input type="hidden" name="app_slug" value="' + esc(app.slug) + '"><input type="hidden" name="country_code" value="' + esc(country.code) + '"><input type="hidden" name="idempotency_key" value="verification-' + Date.now() + '-' + Math.random().toString(36).slice(2) + '"><button class="w-full rounded-xl bg-brand-teal px-4 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Get Number · ' + esc(money(option.price, option.currency, option.price_decimals)) + '</button></form>';
            } else {
                action = '<button disabled class="mt-5 w-full rounded-xl bg-slate-200 px-4 py-3 text-sm font-extrabold text-brand-muted">Unavailable</button>';
            }
            card.innerHTML = '<div class="flex items-start justify-between gap-3"><p class="text-xs font-bold uppercase tracking-wider text-brand-teal">' + esc(app.label) + '</p>' + badge + '</div><h3 class="mt-3 text-lg font-extrabold text-brand-navy">' + esc(option.name) + '</h3><p class="mt-2 line-clamp-3 text-sm text-brand-muted">' + esc(option.summary || 'SMS verification number.') + '</p><div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4"><span class="text-sm text-brand-muted">Price</span><span class="text-lg font-extrabold text-brand-navy">' + esc(money(option.price, option.currency, option.price_decimals)) + '</span></div>' + action;
            optionsEl.appendChild(card);
        });
    }

    document.querySelectorAll('.verification-app').forEach(function (button) {
        button.addEventListener('click', function () {
            state.app = button.dataset.app;
            state.country = null;
            document.querySelectorAll('.verification-app').forEach(function (el) { el.classList.remove('ring-4', 'ring-brand-teal/30'); });
            button.classList.add('ring-4', 'ring-brand-teal/30');
            document.getElementById('verification-selected-app').textContent = selectedApp()?.label || 'this app';
            searchEl.value = '';
            renderCountries('');
            countryStep.classList.remove('hidden');
            optionStep.classList.add('hidden');
            setProgress(2);
            countryStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    searchEl.addEventListener('input', function () { renderCountries(searchEl.value); });
    document.getElementById('verification-back-app').addEventListener('click', function () {
        state.app = null;
        state.country = null;
        document.querySelectorAll('.verification-app').forEach(function (el) { el.classList.remove('ring-4', 'ring-brand-teal/30'); });
        countryStep.classList.add('hidden');
        optionStep.classList.add('hidden');
        setProgress(1);
        appsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    document.getElementById('verification-back-country').addEventListener('click', function () {
        state.country = null;
        renderCountries(searchEl.value);
        optionStep.classList.add('hidden');
        setProgress(2);
        countryStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    setProgress(1);
})();
</script>
@endpush
@endsection
