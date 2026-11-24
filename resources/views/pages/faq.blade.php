@extends('layouts.site')

@section('title', 'FAQ — TrustVerifySMS')
@section('description', 'Frequently asked questions about TrustVerifySMS SMM services, SMS verification, wallet payments and order tracking.')

@section('content')
<section class="bg-gradient-to-br from-brand-navy to-brand-navy-light text-white">
    <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="text-sm font-extrabold uppercase tracking-[0.24em] text-brand-gold">Help centre</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-5xl">Frequently asked questions</h1>
        <p class="mt-4 max-w-3xl text-slate-200">Quick answers for SMM services, verification services, payments and order tracking.</p>
    </div>
</section>

<section class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-2">
        <div>
            <h2 class="text-2xl font-black text-brand-navy">SMM services</h2>
            <div class="mt-5 space-y-4">
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-extrabold text-brand-navy">What are SMM services? <span class="text-brand-teal transition group-open:rotate-180">⌄</span></summary>
                    <p class="mt-3 text-sm leading-6 text-brand-muted">SMM services help grow social channels with followers, likes, views, subscribers, reactions and engagement tools.</p>
                </details>
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-extrabold text-brand-navy">How do I place an SMM order? <span class="text-brand-teal transition group-open:rotate-180">⌄</span></summary>
                    <p class="mt-3 text-sm leading-6 text-brand-muted">Create an account, add funds to your wallet, open the service, choose the product, enter the required details and confirm the order.</p>
                </details>
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-extrabold text-brand-navy">Can I track my SMM order? <span class="text-brand-teal transition group-open:rotate-180">⌄</span></summary>
                    <p class="mt-3 text-sm leading-6 text-brand-muted">Yes. Open My Orders in your account to see the order status, supplier status and delivery information.</p>
                </details>
            </div>
        </div>
        <div>
            <h2 class="text-2xl font-black text-brand-navy">Verification services</h2>
            <div class="mt-5 space-y-4">
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-extrabold text-brand-navy">What are verification services? <span class="text-brand-teal transition group-open:rotate-180">⌄</span></summary>
                    <p class="mt-3 text-sm leading-6 text-brand-muted">Verification services provide SMS verification numbers so you can receive one-time codes for supported platforms.</p>
                </details>
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-extrabold text-brand-navy">How do I receive the SMS code? <span class="text-brand-teal transition group-open:rotate-180">⌄</span></summary>
                    <p class="mt-3 text-sm leading-6 text-brand-muted">After purchase, open the order or Virtual numbers page, use the provided number for verification and check the order status for the received code.</p>
                </details>
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-extrabold text-brand-navy">What if I do not receive a code? <span class="text-brand-teal transition group-open:rotate-180">⌄</span></summary>
                    <p class="mt-3 text-sm leading-6 text-brand-muted">Open a support ticket with the order ID. Support will review the supplier status, expiry window and delivery before deciding the outcome.</p>
                </details>
            </div>
        </div>
    </div>
</section>
@endsection
