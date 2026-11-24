@extends('layouts.site')

@section('title', 'Contact TrustVerifySMS')
@section('description', 'Contact TrustVerifySMS support for orders, payments, verification services and account help.')

@section('content')
<section class="bg-gradient-to-br from-brand-navy to-brand-navy-light text-white">
    <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="text-sm font-extrabold uppercase tracking-[0.24em] text-brand-gold">Contact</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-5xl">We are here to help.</h1>
        <p class="mt-4 max-w-3xl text-slate-200">For order issues, payment questions or verification help, use the support centre or email the team directly.</p>
    </div>
</section>

<section class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="grid gap-5 md:grid-cols-3">
        <a href="mailto:support@trustverifysms.com" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
            <h2 class="text-lg font-extrabold text-brand-navy">Company email</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">support@trustverifysms.com</p>
            <p class="mt-4 text-sm font-extrabold text-brand-teal">Email support →</p>
        </a>
        <a href="{{ route('support') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
            <h2 class="text-lg font-extrabold text-brand-navy">Support centre</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Read help articles, common questions and contact instructions.</p>
            <p class="mt-4 text-sm font-extrabold text-brand-teal">Open support centre →</p>
        </a>
        <a href="{{ route('register') }}" class="rounded-3xl border border-brand-teal/40 bg-brand-teal-soft p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
            <h2 class="text-lg font-extrabold text-brand-navy">Need an account?</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">Create an account to place orders, manage your wallet and open support tickets.</p>
            <p class="mt-4 text-sm font-extrabold text-brand-teal">Create account →</p>
        </a>
    </div>
</section>
@endsection
