@extends('layouts.account')

@section('title', 'New Order | TrustVerifySMS')

@section('content')
@php
    $requiresTargetLink = $product->type === 'smm_service' && ($product->supplier_adapter ?: $product->supplier) === 'justanotherpanel';
    $pricingUnit = $product->pricingUnit();
    $decimals = $product->priceDecimals();
    $quickAmounts = [100, 500, 1000, 2500];
@endphp

<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Checkout</p>
        <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">{{ $product->name }}</h1>
        <p class="mt-2 text-brand-muted">{{ $product->summary }}</p>
    </div>

    <div class="flex flex-wrap gap-2 text-xs font-bold">
        <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-brand-navy">1 · Service selected</span>
        <span class="rounded-full bg-brand-navy px-3 py-1 text-white">2 · Quantity & details</span>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-brand-muted">3 · Confirm payment</span>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-order-form
         data-unit-price="{{ (float) $product->price }}"
         data-currency="{{ $product->currency ?: 'XAF' }}"
         data-pricing-unit="{{ $pricingUnit }}"
         data-decimals="{{ $decimals }}">
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

        <form method="POST" action="{{ route('dashboard.orders.store', $product) }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">

            <div>
                <label class="text-sm font-bold text-brand-text" for="quantity">How many?</label>
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

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-brand-muted">Estimated total</p>
                        <p id="order-total" class="mt-1 text-2xl font-extrabold text-brand-navy">—</p>
                    </div>
                    <p class="max-w-xs text-right text-xs text-brand-muted">Wallet checkout protects your balance and prevents duplicate charges.</p>
                </div>
            </div>

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

            @error('order')<div class="rounded-xl border border-brand-error/20 bg-brand-error/10 px-4 py-3 text-sm font-semibold text-brand-error">{{ $message }}</div>@enderror

            <button type="submit" class="w-full rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Place order</button>
            <p class="text-xs text-brand-muted">Your wallet balance will be checked and charged once. Duplicate submissions are protected by an idempotency key.</p>
        </form>
    </div>
</div>

<script>
(function () {
    const form = document.querySelector('[data-order-form]');
    const quantity = document.getElementById('quantity');
    const total = document.getElementById('order-total');
    if (!form || !quantity || !total) return;

    const unitPrice = parseFloat(form.dataset.unitPrice || '0');
    const currency = form.dataset.currency || 'XAF';
    const per1000 = form.dataset.pricingUnit === 'per_1000';
    const decimals = parseInt(form.dataset.decimals || '0', 10);

    function format(value) {
        return value.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + ' ' + currency;
    }

    function updateTotal() {
        const qty = Math.max(0, parseInt(quantity.value || '0', 10) || 0);
        const amount = per1000 ? (unitPrice * qty) / 1000 : unitPrice * qty;
        total.textContent = qty > 0 ? format(amount) : '—';
    }

    document.querySelectorAll('.quick-amount').forEach(function (button) {
        button.addEventListener('click', function () {
            quantity.value = button.dataset.quantity;
            updateTotal();
        });
    });

    quantity.addEventListener('input', updateTotal);
    updateTotal();
})();
</script>
@endsection
