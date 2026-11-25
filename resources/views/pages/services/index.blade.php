@extends('layouts.site')

@php
    $isSmm = $selectedService === 'smm_service';
    $pageTitle = $isSmm ? 'SMM Services | TrustVerifySMS' : 'Verification Services | TrustVerifySMS';
    $pageDescription = $isSmm ? 'Choose a platform and start growing with SMM services.' : 'Choose a verification service and check availability before checkout.';
    $heading = $isSmm ? 'Choose a platform and start growing.' : 'Choose a verification service.';
    $subheading = $isSmm ? '' : 'Pick a verification product, review price and availability, then complete checkout from your wallet.';
@endphp

@section('title', $pageTitle)
@section('description', $pageDescription)

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">{{ $isSmm ? 'SMM Services' : 'Verification Services' }}</p>
            <h1 class="mt-2 text-4xl font-extrabold text-brand-navy">{{ $heading }}</h1>
            @if($subheading)<p class="mt-3 max-w-2xl text-brand-muted">{{ $subheading }}</p>@endif
        </div>
        @if($selectedService !== 'smm_service' || $selectedGroup)
            <form method="GET" action="{{ route('services.index') }}" class="flex w-full max-w-xl gap-2">
                <input type="hidden" name="service" value="{{ $selectedService }}">
                @if($selectedService === 'smm_service' && $selectedBrandSlug)<input type="hidden" name="brand" value="{{ $selectedBrandSlug }}">@endif
                @if($selectedGroupSlug)<input type="hidden" name="group" value="{{ $selectedGroupSlug }}">@endif
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search services..." class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal">
                <button class="rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Search</button>
            </form>
        @endif
    </div>

    @if($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-brand-error">{{ $errors->first() }}</div>
    @endif

    @if(! $isSmm)
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('services.index') }}">
                <label for="service" class="text-sm font-bold text-brand-navy">Catalogue</label>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <select id="service" name="service" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal" onchange="this.form.submit()">
                        @foreach($serviceOptions as $option)
                            <option value="{{ $option['slug'] }}" @selected($selectedService === $option['slug'])>{{ $option['label'] }}{{ $option['available'] ? '' : ' (Coming soon)' }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Apply</button>
                </div>
            </form>
        </div>
    @endif

    @if($selectedService === 'smm_service')
        @if($awaitingBrand)
            <div id="smm-explorer" class="mt-8" data-endpoint="{{ route('services.smm-products') }}" data-authenticated="{{ auth()->check() ? '1' : '0' }}" data-csrf="{{ csrf_token() }}" data-login="{{ route('login') }}">
                <div data-smm-level="platforms">
                    <div class="flex flex-wrap gap-3">
                        @forelse($brandOptions as $brand)
                            <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $brand['slug']]) }}" data-smm-brand="{{ $brand['slug'] }}" data-smm-brand-label="{{ $brand['label'] }}" data-smm-brand-logo="{{ $brand['logo'] }}" data-smm-brand-color="{{ ltrim($brand['color'] ?? '071C1A', '#') }}" title="{{ $brand['label'] }} · {{ number_format($brand['product_count']) }} services" aria-label="Choose {{ $brand['label'] }} services" class="group flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-teal/60 hover:shadow-md">
                                <img src="https://cdn.simpleicons.org/{{ $brand['logo'] }}/{{ ltrim($brand['color'] ?? '071C1A', '#') }}" alt="{{ $brand['label'] }} logo" class="h-8 w-8" loading="lazy">
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-brand-muted">No SMM platforms are available yet.</div>
                        @endforelse
                    </div>
                </div>

                <div data-smm-level="categories" class="mt-8 hidden">
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" data-smm-back="platforms" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← Platforms</button>
                        <div data-smm-category-brand class="flex items-center gap-3"></div>
                    </div>
                    <div data-smm-categories class="mt-5 flex flex-wrap gap-2"></div>
                </div>

                <div data-smm-level="services" class="mt-8 hidden">
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" data-smm-back="categories" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← Categories</button>
                        <h2 id="smm-service-title" class="text-2xl font-extrabold text-brand-navy"></h2>
                        <span id="smm-service-count" class="rounded-full bg-brand-teal-soft px-3 py-1 text-xs font-bold text-brand-navy"></span>
                    </div>
                    <div data-smm-services class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3"></div>
                    <div data-smm-service-empty class="mt-5 hidden rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-brand-muted">No services are available for this category.</div>
                </div>

                <div data-smm-level="order" class="mt-8 hidden">
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" data-smm-back="services" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← Services</button>
                        <h2 id="smm-order-title" class="text-2xl font-extrabold text-brand-navy"></h2>
                    </div>
                    <div id="smm-order-panel" class="mt-5"></div>
                </div>
            </div>

            <script>
            window.smmSummaries = @json($smmSummaries ?? []);
            (function () {
                const explorer = document.getElementById('smm-explorer');
                if (! explorer) return;

                const endpoint = explorer.dataset.endpoint;
                const loginUrl = explorer.dataset.login;
                const authenticated = explorer.dataset.authenticated === '1';
                const csrf = explorer.dataset.csrf || '';
                const summaries = window.smmSummaries || {};
                const levels = {};
                explorer.querySelectorAll('[data-smm-level]').forEach((level) => { levels[level.dataset.smmLevel] = level; });

                let currentBrand = null;
                let currentGroup = null;
                let currentProducts = [];
                let currentProduct = null;

                const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
                const money = (value, decimals) => Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
                const uuid = () => (window.crypto && window.crypto.randomUUID) ? window.crypto.randomUUID() : `${Date.now()}-${Math.random().toString(16).slice(2)}`;
                const priceText = (product) => `${money(product.price, product.decimals)} ${product.currency}${product.is_per_1000 ? ' / 1,000' : ''}`;

                function show(level) {
                    Object.keys(levels).forEach((key) => levels[key].classList.toggle('hidden', key !== level));
                }

                explorer.querySelectorAll('[data-smm-back]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const target = button.dataset.smmBack;
                        if (target === 'platforms') { currentBrand = null; currentGroup = null; currentProducts = []; currentProduct = null; }
                        if (target === 'categories') { currentGroup = null; currentProducts = []; currentProduct = null; }
                        if (target === 'services') { currentProducts = []; currentProduct = null; }
                        show(target);
                    });
                });

                explorer.querySelectorAll('[data-smm-brand]').forEach((link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        selectBrand(link);
                    });
                });

                function selectBrand(link) {
                    currentBrand = { slug: link.dataset.smmBrand, label: link.dataset.smmBrandLabel, logo: link.dataset.smmBrandLogo, color: link.dataset.smmBrandColor };
                    currentGroup = null;
                    currentProducts = [];
                    currentProduct = null;

                    const brandBox = explorer.querySelector('[data-smm-category-brand]');
                    brandBox.innerHTML = `<span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 p-1.5"><img src="https://cdn.simpleicons.org/${esc(currentBrand.logo)}/${esc(currentBrand.color)}" alt="" class="h-6 w-6"></span><h2 class="text-2xl font-extrabold text-brand-navy">${esc(currentBrand.label)} services</h2>`;

                    const wrap = explorer.querySelector('[data-smm-categories]');
                    const items = Object.values(summaries[currentBrand.slug] || {});
                    wrap.innerHTML = items.length
                        ? items.map((summary) => `<button type="button" data-smm-group="${esc(summary.slug)}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-brand-navy shadow-sm transition hover:border-brand-teal/60 hover:text-brand-teal">${esc(summary.label)} <span class="rounded-full bg-brand-teal-soft px-2 py-0.5 text-xs text-brand-navy">${Number(summary.count || 0).toLocaleString('en-US')}</span></button>`).join('')
                        : '<div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-brand-muted">No categories are available for this platform.</div>';

                    wrap.querySelectorAll('[data-smm-group]').forEach((button) => button.addEventListener('click', () => selectGroup(button.dataset.smmGroup)));
                    show('categories');
                }

                function selectGroup(groupSlug) {
                    const groups = summaries[currentBrand.slug] || {};
                    currentGroup = groups[groupSlug] || { slug: groupSlug, label: groupSlug.replace(/_/g, ' ') };
                    currentProducts = [];
                    currentProduct = null;

                    document.getElementById('smm-service-title').textContent = currentGroup.label;
                    const servicesBox = explorer.querySelector('[data-smm-services]');
                    const empty = explorer.querySelector('[data-smm-service-empty]');
                    document.getElementById('smm-service-count').textContent = '';
                    empty.classList.add('hidden');
                    servicesBox.innerHTML = '<div class="col-span-full rounded-2xl border border-slate-200 bg-white p-8 text-center text-brand-muted">Loading services…</div>';
                    show('services');

                    fetch(`${endpoint}?brand=${encodeURIComponent(currentBrand.slug)}&group=${encodeURIComponent(groupSlug)}`, { headers: { 'Accept': 'application/json' } })
                        .then((response) => {
                            if (! response.ok) throw new Error('Unable to load services.');
                            return response.json();
                        })
                        .then((data) => {
                            currentProducts = data.products || [];
                            renderServices();
                        })
                        .catch(() => {
                            servicesBox.innerHTML = '<div class="col-span-full rounded-2xl border border-red-200 bg-red-50 p-8 text-center text-brand-error">Unable to load services right now.</div>';
                        });
                }

                function renderServices() {
                    const grid = explorer.querySelector('[data-smm-services]');
                    const empty = explorer.querySelector('[data-smm-service-empty]');
                    const count = explorer.querySelector('[data-smm-service-count]');

                    count.textContent = currentProducts.length ? `${currentProducts.length} service${currentProducts.length === 1 ? '' : 's'}` : '';
                    empty.classList.toggle('hidden', currentProducts.length > 0);

                    grid.innerHTML = currentProducts.map((product, index) => {
                        const delivery = product.delivery ? `<div class="col-span-2"><span class="block text-brand-muted">Est. delivery</span><span class="font-semibold text-brand-text">${esc(product.delivery)}</span></div>` : '';
                        return `<button type="button" data-smm-product-index="${index}" class="w-full rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-xs font-bold uppercase tracking-wider text-brand-teal">${esc(currentBrand.label)}</p>
                                <span class="rounded-full px-2 py-1 text-xs font-bold ${product.available ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted'}">${product.available ? 'Available' : 'Unavailable'}</span>
                            </div>
                            <h3 class="mt-3 line-clamp-2 text-lg font-extrabold text-brand-navy">${esc(product.name)}</h3>
                            <p class="mt-2 line-clamp-3 text-sm text-brand-muted">${esc(product.summary || '')}</p>
                            <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-4 text-sm">
                                <div><span class="block text-brand-muted">Price</span><span class="font-extrabold text-brand-navy">${esc(priceText(product))}</span></div>
                                <div><span class="block text-brand-muted">Min order</span><span class="font-semibold text-brand-text">${Number(product.min_order || 1).toLocaleString('en-US')}</span></div>
                                <div><span class="block text-brand-muted">Max order</span><span class="font-semibold text-brand-text">${product.max_order ? Number(product.max_order).toLocaleString('en-US') : 'Unlimited'}</span></div>${delivery}
                            </div>
                            <p class="mt-4 text-sm font-bold text-brand-teal">Select service →</p>
                        </button>`;
                    }).join('');

                    grid.querySelectorAll('[data-smm-product-index]').forEach((button) => button.addEventListener('click', () => selectProduct(Number(button.dataset.smmProductIndex))));
                }

                function selectProduct(index) {
                    currentProduct = currentProducts[index];
                    if (! currentProduct) return;
                    document.getElementById('smm-order-title').textContent = currentProduct.name;
                    renderOrderPanel();
                    show('order');
                }

                function factsHtml(product) {
                    const delivery = product.delivery ? `<div><dt class="text-xs font-bold uppercase text-brand-muted">Est. delivery</dt><dd class="mt-1 text-sm font-semibold text-brand-text">${esc(product.delivery)}</dd></div>` : '';
                    return `<dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                        <div><dt class="text-xs font-bold uppercase text-brand-muted">Price</dt><dd class="mt-1 font-extrabold text-brand-navy">${esc(priceText(product))}</dd></div>
                        <div><dt class="text-xs font-bold uppercase text-brand-muted">Min order</dt><dd class="mt-1 font-semibold text-brand-text">${Number(product.min_order || 1).toLocaleString('en-US')}</dd></div>
                        <div><dt class="text-xs font-bold uppercase text-brand-muted">Max order</dt><dd class="mt-1 font-semibold text-brand-text">${product.max_order ? Number(product.max_order).toLocaleString('en-US') : 'Unlimited'}</dd></div>${delivery}
                    </dl>`;
                }

                function fieldsHtml(product) {
                    const quick = product.is_per_1000 ? `<div class="mt-3 flex flex-wrap gap-2">${[100, 500, 1000, 2500].map((amount) => `<button type="button" class="smm-quick rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-brand-navy hover:border-brand-teal hover:bg-brand-teal-soft" data-quantity="${amount}">${amount.toLocaleString('en-US')}</button>`).join('')}</div>` : '';
                    return `<div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-bold text-brand-text" for="smm-quantity">Enter Amount</label>
                            ${quick}
                            <input id="smm-quantity" name="quantity" type="number" min="${product.min_order}" max="${product.max_order || 1000000}" value="${product.min_order}" required class="mt-3 w-full rounded-xl border-slate-300 px-4 py-3">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-brand-text" for="smm-link">Target link / username${product.requires_link ? ' *' : ''}</label>
                            <input id="smm-link" name="link" type="text" maxlength="2048" ${product.requires_link ? 'required' : ''} placeholder="https://..." class="mt-3 w-full rounded-xl border-slate-300 px-4 py-3">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-brand-text" for="smm-service">Service</label>
                            <input id="smm-service" name="service" type="text" maxlength="120" placeholder="Optional" class="mt-3 w-full rounded-xl border-slate-300 px-4 py-3">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-brand-text" for="smm-notes">Notes</label>
                            <textarea id="smm-notes" name="notes" maxlength="2000" rows="3" placeholder="Optional" class="mt-3 w-full rounded-xl border-slate-300 px-4 py-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col gap-3 rounded-2xl bg-brand-bg p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div><p class="text-sm text-brand-muted">Total</p><p id="smm-total" class="text-2xl font-extrabold text-brand-navy"></p></div>
                        <p id="smm-order-error" class="hidden text-sm font-bold text-brand-error"></p>
                    </div>`;
                }

                function renderOrderPanel() {
                    const product = currentProduct;
                    const panel = document.getElementById('smm-order-panel');
                    const details = `<div class="max-w-2xl">
                        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">${esc(currentBrand.label)} · ${esc(currentGroup ? currentGroup.label : '')}</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-brand-navy">${esc(product.name)}</h3>
                        <p class="mt-2 text-sm text-brand-muted">${esc(product.summary || '')}</p>
                        ${factsHtml(product)}
                    </div>`;

                    if (authenticated) {
                        panel.innerHTML = `<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">${details}<form method="POST" action="${esc(product.order_url)}" class="w-full max-w-xl space-y-5">${fieldsHtml(product)}<input type="hidden" name="_token" value="${esc(csrf)}"><input type="hidden" name="idempotency_key" value="${uuid()}"><button id="smm-order-submit" type="submit" class="w-full rounded-xl bg-brand-navy px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-navy-light">Order Now</button></form></div></div>`;
                    } else {
                        panel.innerHTML = `<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">${details}<div class="w-full max-w-xl">${fieldsHtml(product)}<a href="${esc(loginUrl)}" class="mt-5 block w-full rounded-xl bg-brand-navy px-5 py-3 text-center text-sm font-extrabold text-white hover:bg-brand-navy-light">Sign in to order</a><p class="mt-2 text-center text-xs text-brand-muted">Sign in to place this order from your wallet.</p></div></div></div>`;
                    }

                    bindOrder(product);
                }

                function bindOrder(product) {
                    const quantity = document.getElementById('smm-quantity');
                    const total = document.getElementById('smm-total');
                    const error = document.getElementById('smm-order-error');
                    const submit = document.getElementById('smm-order-submit');
                    const min = Number(product.min_order || 1);
                    const max = product.max_order ? Number(product.max_order) : 1000000;

                    const update = () => {
                        const qty = Number(quantity.value || 0);
                        const valid = qty >= min && qty <= max;
                        const value = product.is_per_1000 ? product.price * qty / 1000 : product.price * qty;
                        total.textContent = `${money(value, product.decimals)} ${product.currency}`;
                        error.textContent = valid ? '' : `Enter an amount between ${min.toLocaleString('en-US')} and ${max.toLocaleString('en-US')}.`;
                        error.classList.toggle('hidden', valid);
                        if (submit) submit.disabled = ! valid;
                    };

                    quantity.addEventListener('input', update);
                    document.querySelectorAll('.smm-quick').forEach((button) => button.addEventListener('click', () => {
                        quantity.value = button.dataset.quantity;
                        update();
                    }));
                    update();
                }
            })();
            </script>
        @elseif($brandComingSoon)
            <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Coming soon</p>
                <h2 class="mt-3 text-3xl font-extrabold text-brand-navy">{{ $selectedBrand['label'] }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-brand-muted">Price-checked services for this platform are not available yet. We will publish them only when the catalogue and pricing are verified.</p>
                <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="mt-6 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Choose another platform</a>
            </div>
        @elseif($groupComingSoon)
            <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Coming soon</p>
                <h2 class="mt-3 text-3xl font-extrabold text-brand-navy">{{ $selectedGroup['label'] }} on {{ $selectedBrand['label'] }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-brand-muted">This service is not available yet at a verified price. We will only show it when supplier pricing is confirmed.</p>
                <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $selectedBrandSlug]) }}" class="mt-6 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Back to {{ $selectedBrand['label'] }} services</a>
            </div>
        @elseif($selectedGroup)
            <div class="mt-8">
                <div class="mb-5 flex flex-wrap items-center gap-3">
                    <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $selectedBrandSlug]) }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← {{ $selectedBrand['label'] }} services</a>
                    <h2 class="text-2xl font-extrabold text-brand-navy">{{ $selectedGroup['label'] }} on {{ $selectedBrand['label'] }}</h2>
                    <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-xs font-bold text-brand-navy">{{ number_format($products->total()) }} options</span>
                </div>

                @if($products->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-brand-muted">No options match your search.</div>
                @else
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <a href="{{ route('services.show', $product) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-bold uppercase tracking-wider text-brand-teal">{{ $selectedBrand['label'] }}</p>
                                    <span class="rounded-full px-2 py-1 text-xs font-bold {{ $product->isAvailable() ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted' }}">{{ $product->isAvailable() ? 'Available' : 'Unavailable' }}</span>
                                </div>
                                <h3 class="mt-3 line-clamp-2 text-lg font-extrabold text-brand-navy">{{ $product->name }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm text-brand-muted">{{ $product->summary }}</p>
                                @php($delivery = $product->estimatedDelivery())
                                <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-4 text-sm">
                                    <div><span class="block text-brand-muted">Price</span><span class="font-extrabold text-brand-navy">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</span></div>
                                    <div><span class="block text-brand-muted">Min order</span><span class="font-semibold text-brand-text">{{ number_format($product->min_order ?? 1) }}</span></div>
                                    <div><span class="block text-brand-muted">Max order</span><span class="font-semibold text-brand-text">{{ $product->max_order ? number_format($product->max_order) : 'Unlimited' }}</span></div>
                                    @if($delivery)<div class="col-span-2"><span class="block text-brand-muted">Est. delivery</span><span class="font-semibold text-brand-text">{{ $delivery }}</span></div>@endif
                                </div>
                                <p class="mt-4 text-sm font-bold text-brand-teal">Select service →</p>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $products->links() }}</div>
                @endif
            </div>
        @else
            <div class="mt-8">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="text-sm font-bold text-brand-teal hover:text-brand-gold">← Platforms</a>
                    <div class="flex flex-wrap gap-2" aria-label="SMM platforms">
                        @foreach($brandOptions as $platform)
                            <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $platform['slug']]) }}" title="{{ $platform['label'] }}" aria-label="{{ $platform['label'] }}{{ $platform['slug'] === $selectedBrandSlug ? ' (selected)' : '' }}" class="flex h-11 w-11 items-center justify-center rounded-xl border bg-white p-1.5 transition {{ $platform['slug'] === $selectedBrandSlug ? 'border-brand-teal ring-2 ring-brand-teal/30' : 'border-slate-200 hover:border-brand-teal/60 hover:shadow-sm' }}">
                                <img src="https://cdn.simpleicons.org/{{ $platform['logo'] }}/{{ ltrim($platform['color'] ?? '071C1A', '#') }}" alt="" class="h-6 w-6" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-2xl font-extrabold text-brand-navy">{{ $selectedBrand['label'] }} services</h2>
                    <p class="mt-1 text-sm text-brand-muted">Choose a category. Only categories with verified JAP services are shown.</p>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach($brandSummaries as $summary)
                        <a href="{{ route('services.index', ['service' => 'smm_service', 'brand' => $selectedBrandSlug, 'group' => $summary['slug']]) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-brand-navy shadow-sm transition hover:border-brand-teal/60 hover:text-brand-teal">
                            {{ $summary['label'] }} <span class="rounded-full bg-brand-teal-soft px-2 py-0.5 text-xs text-brand-navy">{{ number_format($summary['count']) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @elseif($comingSoon)
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Coming soon</p>
            <h2 class="mt-3 text-3xl font-extrabold text-brand-navy">{{ $selectedServiceLabel }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-brand-muted">This service is not available yet. We are preparing verified inventory and pricing, and it will appear here as soon as it is ready.</p>
            <a href="{{ route('services.index', ['service' => 'smm_service']) }}" class="mt-6 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Browse SMM services</a>
        </div>
    @else
        <div class="mt-8 grid gap-8 lg:grid-cols-[240px_1fr]">
            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-extrabold text-brand-navy">Categories</h2>
                <form method="GET" class="mt-4 space-y-2">
                    <input type="hidden" name="service" value="{{ $selectedService }}">
                    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                    <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-brand-bg">
                        <input type="radio" name="category_id" value="" {{ ! request('category_id') ? 'checked' : '' }} class="text-brand-teal focus:ring-brand-teal">
                        <span>All categories</span>
                    </label>
                    @foreach($categories as $category)
                        <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-brand-bg">
                            <input type="radio" name="category_id" value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'checked' : '' }} class="text-brand-teal focus:ring-brand-teal">
                            <span>{{ $category->name }}</span>
                        </label>
                    @endforeach
                    <button class="mt-3 w-full rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Apply</button>
                </form>
            </aside>

            <div>
                @if($products->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-brand-muted">No active products match your filters.</div>
                @else
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <a href="{{ route('services.show', $product) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-bold uppercase tracking-wider text-brand-teal">{{ $product->category?->name ?? str_replace('_', ' ', $product->type) }}</p>
                                    <span class="rounded-full px-2 py-1 text-xs font-bold {{ $product->isAvailable() ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted' }}">{{ $product->isAvailable() ? 'Available' : 'Unavailable' }}</span>
                                </div>
                                <h3 class="mt-3 text-lg font-extrabold text-brand-navy">{{ $product->name }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm text-brand-muted">{{ $product->summary }}</p>
                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                    <span class="text-sm font-bold text-brand-navy">{{ number_format((float) $product->price, $product->priceDecimals()) }} {{ $product->currency }}{{ $product->priceSuffix() }}</span>
                                    <span class="text-xs font-semibold text-brand-muted">Min {{ $product->min_order ?? 1 }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $products->links() }}</div>
                @endif
            </div>
        </div>
    @endif
</section>
@endsection
