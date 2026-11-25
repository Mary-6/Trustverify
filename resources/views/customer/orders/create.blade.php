@extends('layouts.account')

@section('title', 'New Order | TrustVerifySMS')

@section('content')
@php
    $requiresTargetLink = $product->type === 'smm_service' && ($product->supplier_adapter ?: $product->supplier) === 'justanotherpanel';
    $pricingUnit = $product->pricingUnit();
    $decimals = $product->priceDecimals();
    $quickAmounts = [100, 500, 1000, 2500];
    $initialStep = $errors->has('order') ? 4 : ($errors->any() ? 2 : 1);
@endphp

<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Checkout</p>
        <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">{{ $product->name }}</h1>
        <p class="mt-2 text-brand-muted">{{ $product->summary }}</p>
    </div>

    <div class="flex flex-wrap gap-2 text-xs font-bold" aria-label="Order progress">
        <span data-step-chip="1" class="rounded-full px-3 py-1">1 · Service selected</span>
        <span data-step-chip="2" class="rounded-full px-3 py-1">2 · Quantity & details</span>
        <span data-step-chip="3" class="rounded-full px-3 py-1">3 · Review total</span>
        <span data-step-chip="4" class="rounded-full px-3 py-1">4 · Confirm payment</span>
    </div>

    @if($product->type === 'virtual_number')
        <div class="rounded-2xl border border-brand-teal/30 bg-brand-teal-soft p-4 text-sm leading-6 text-brand-text">
            Verification product: after purchase, use the number shown in your order/Virtual numbers page to receive the SMS code.
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-order-form
         data-unit-price="{{ (float) $product->price }}"
         data-currency="{{ $product->currency ?: 'XAF' }}"
         data-pricing-unit="{{ $pricingUnit }}"
         data-decimals="{{ $decimals }}"
         data-requires-link="{{ $requiresTargetLink ? '1' : '0' }}"
         data-min="{{ $product->min_order ?? 1 }}"
         data-max="{{ $product->max_order ?? 1000000 }}">
        <form method="POST" action="{{ route('dashboard.orders.store', $product) }}" class="mt-2 space-y-5">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">

            <div data-step-panel="1">
                <div class="flex items-center justify-between rounded-2xl bg-brand-bg p-4">
                    <div>
                        <p class="text-sm text-brand-muted">{{ $product->isPer1000() ? 'Price per 1,000' : 'Unit price' }}</p>
                        <p class="text-xl font-extrabold text-brand-navy">{{ number_format((float) $product->price, $decimals) }} {{ $product->currency ?: 'XAF' }}{{ $product->priceSuffix() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-brand-muted">Quantity limits</p>
                        <p class="text-sm font-bold text-brand-navy">{{ number_format($product->min_order ?? 1) }} – {{ $product->max_order ? number_format($product->max_order) : '∞' }}</p>
                    </div>
                </div>
                <button type="button" data-next-step="2" class="mt-5 w-full rounded-xl bg-brand-navy px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-navy-light">Continue</button>
            </div>

            <div data-step-panel="2" class="hidden space-y-5">
                @if($product->type === 'smm_service')
                <div>
                    <label class="text-sm font-bold text-brand-text" for="quantity">Enter Amount</label>
                    @if($product->isPer1000())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($quickAmounts as $amount)
                                <button type="button" class="quick-amount rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-brand-navy hover:border-brand-teal hover:bg-brand-teal-soft" data-quantity="{{ $amount }}">{{ number_format($amount) }}</button>
                            @endforeach
                        </div>
                    @endif
                    <input id="quantity" name="quantity" type="number" min="{{ $product->min_order ?? 1 }}" max="{{ $product->max_order ?? 1000000 }}" value="{{ old('quantity', $product->min_order ?? 1) }}" required class="mt-3 w-full rounded-xl border-slate-300 px-4 py-3">
                    @error('quantity')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
                </div>
                @else
                <input id="quantity" name="quantity" type="hidden" value="1">
                <div class="rounded-2xl border border-brand-teal/30 bg-brand-teal-soft p-4 text-sm font-semibold text-brand-text">This verification service uses one number per order, so no amount is required.</div>
                @endif

                @if($product->type === 'virtual_number')
                    <div>
                        <label class="text-sm font-bold text-brand-text" for="country_id">Country</label>
                        <select id="country_id" name="country_id" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                            <option value="">Select country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" @selected(old('country_id') == $country->id)>{{ $country->name }} ({{ $country->dial_code }})</option>
                            @endforeach
                        </select>
                        @error('country_id')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
                    </div>
                @endif

                <div>
                    <label class="text-sm font-bold text-brand-text" for="service">Service / details</label>
                    <input id="service" name="service" value="{{ old('service') }}" placeholder="Optional service identifier or destination" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                    @error('service')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-bold text-brand-text" for="link">Link / target @if($requiresTargetLink) <span class="text-red-600">*</span> @endif</label>
                    <input id="link" name="link" type="{{ $requiresTargetLink ? 'url' : 'text' }}" value="{{ old('link') }}" placeholder="https://..." class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" @if($requiresTargetLink) required @endif>
                    @error('link')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-bold text-brand-text" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3" placeholder="Optional order notes">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-brand-error">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-brand-muted">Estimated total</p>
                            <p id="order-total" class="mt-1 text-2xl font-extrabold text-brand-navy">—</p>
                        </div>
                        <p class="max-w-xs text-right text-xs text-brand-muted">Wallet checkout protects your balance and prevents duplicate charges.</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" data-prev-step="1" class="flex-1 rounded-xl border border-slate-300 px-5 py-3 text-sm font-extrabold text-brand-navy hover:border-brand-teal">Back</button>
                    <button type="button" data-next-step="3" class="flex-1 rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Review order</button>
                </div>
            </div>

            <div data-step-panel="3" class="hidden space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-lg font-extrabold text-brand-navy">Review your order</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4"><dt class="text-brand-muted">Service</dt><dd class="text-right font-bold text-brand-navy">{{ $product->name }}</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-brand-muted">Quantity</dt><dd id="review-quantity" class="font-bold text-brand-navy">—</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-brand-muted">Unit price</dt><dd class="font-bold text-brand-navy">{{ number_format((float) $product->price, $decimals) }} {{ $product->currency ?: 'XAF' }}{{ $product->priceSuffix() }}</dd></div>
                        <div class="flex items-center justify-between gap-4 border-t border-slate-200 pt-3"><dt class="font-extrabold text-brand-navy">Estimated total</dt><dd id="order-total-review" class="text-lg font-extrabold text-brand-navy">—</dd></div>
                    </dl>
                </div>
                <div class="flex gap-3">
                    <button type="button" data-prev-step="2" class="flex-1 rounded-xl border border-slate-300 px-5 py-3 text-sm font-extrabold text-brand-navy hover:border-brand-teal">Back</button>
                    <button type="button" data-next-step="4" class="flex-1 rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Continue to payment</button>
                </div>
            </div>

            <div data-step-panel="4" class="hidden space-y-5">
                <div class="rounded-2xl border border-brand-teal/30 bg-brand-teal-soft p-5 text-sm leading-6 text-brand-text">
                    <h2 class="font-extrabold text-brand-navy">Confirm payment</h2>
                    <p class="mt-2">Your wallet balance will be checked and charged once. Duplicate submissions are protected by an idempotency key, so retrying safely will not charge you twice.</p>
                </div>

                @error('order')<div class="rounded-xl border border-brand-error/20 bg-brand-error/10 px-4 py-3 text-sm font-semibold text-brand-error">{{ $message }}</div>@enderror

                <div class="flex gap-3">
                    <button type="button" data-prev-step="3" class="flex-1 rounded-xl border border-slate-300 px-5 py-3 text-sm font-extrabold text-brand-navy hover:border-brand-teal">Back</button>
                    <button type="submit" class="flex-1 rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Order Now</button>
                </div>
                <p class="text-xs text-brand-muted">By placing this order you confirm the service details are correct.</p>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const form = document.querySelector('[data-order-form]');
    const quantity = document.getElementById('quantity');
    const total = document.getElementById('order-total');
    const totalReview = document.getElementById('order-total-review');
    const quantityReview = document.getElementById('review-quantity');
    const link = document.getElementById('link');
    if (!form || !quantity || !total) return;

    const unitPrice = parseFloat(form.dataset.unitPrice || '0');
    const currency = form.dataset.currency || 'XAF';
    const per1000 = form.dataset.pricingUnit === 'per_1000';
    const decimals = parseInt(form.dataset.decimals || '0', 10);
    const requiresLink = form.dataset.requiresLink === '1';
    const minQty = parseInt(form.dataset.min || '1', 10);
    const maxQty = parseInt(form.dataset.max || '1000000', 10);
    const initialStep = {{ $initialStep }};

    const chips = Array.from(document.querySelectorAll('[data-step-chip]'));
    const panels = Array.from(document.querySelectorAll('[data-step-panel]'));

    function format(value) {
        return value.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + ' ' + currency;
    }

    function currentTotal() {
        const qty = Math.max(0, parseInt(quantity.value || '0', 10) || 0);
        return per1000 ? (unitPrice * qty) / 1000 : unitPrice * qty;
    }

    function updateTotal() {
        const qty = Math.max(0, parseInt(quantity.value || '0', 10) || 0);
        const amount = currentTotal();
        const label = qty > 0 ? format(amount) : '—';
        total.textContent = label;
        if (totalReview) totalReview.textContent = label;
        if (quantityReview) quantityReview.textContent = qty > 0 ? qty.toLocaleString('en-US') : '—';
    }

    function stepIsValid(step) {
        if (step !== 2) return true;
        const qty = parseInt(quantity.value || '0', 10);
        if (!Number.isFinite(qty) || qty < minQty || qty > maxQty) {
            quantity.focus();
            return false;
        }
        if (requiresLink && link && link.value.trim() === '') {
            link.focus();
            return false;
        }
        return true;
    }

    function goToStep(step) {
        panels.forEach(function (panel) {
            panel.classList.toggle('hidden', panel.dataset.stepPanel !== String(step));
        });
        chips.forEach(function (chip) {
            const index = parseInt(chip.dataset.stepChip, 10);
            chip.className = 'rounded-full px-3 py-1 text-xs font-bold ' + (
                index < step ? 'bg-brand-teal-soft text-brand-navy' :
                index === step ? 'bg-brand-navy text-white' :
                'bg-slate-100 text-brand-muted'
            );
        });
    }

    document.querySelectorAll('[data-next-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            const next = parseInt(button.dataset.nextStep, 10);
            const current = parseInt(next - 1, 10);
            if (!stepIsValid(current)) return;
            updateTotal();
            goToStep(next);
        });
    });

    document.querySelectorAll('[data-prev-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            goToStep(parseInt(button.dataset.prevStep, 10));
        });
    });

    document.querySelectorAll('.quick-amount').forEach(function (button) {
        button.addEventListener('click', function () {
            quantity.value = button.dataset.quantity;
            updateTotal();
        });
    });

    quantity.addEventListener('input', updateTotal);
    updateTotal();
    goToStep(initialStep);
})();
</script>
@endsection
