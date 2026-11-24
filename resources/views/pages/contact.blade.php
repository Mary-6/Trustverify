@extends('layouts.site')

@section('title', 'Contact TrustVerifySMS')
@section('description', 'Contact TrustVerifySMS support for orders, payments, verification services and account help.')

@section('content')
<section class="bg-gradient-to-br from-brand-navy to-brand-navy-light text-white">
    <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="text-sm font-extrabold uppercase tracking-[0.24em] text-brand-gold">Contact</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-5xl">We are here to help.</h1>
        <p class="mt-4 max-w-3xl text-slate-200">Send us a message for order issues, payment questions, verification help or account support. Include your order ID when asking about an order.</p>
    </div>
</section>

<section class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="grid gap-5 md:grid-cols-3">
        <a href="mailto:{{ $contact['email'] }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
            <h2 class="text-lg font-extrabold text-brand-navy">Email</h2>
            <p class="mt-3 break-all text-sm leading-6 text-brand-muted">{{ $contact['email'] }}</p>
            <p class="mt-4 text-sm font-extrabold text-brand-teal">Email support →</p>
        </a>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-brand-navy">Phone</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">{{ $contact['phone'] ?: 'Not publicly listed — email us for business contact details.' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-brand-navy">Address</h2>
            <p class="mt-3 text-sm leading-6 text-brand-muted">{{ $contact['address'] ?: 'Not publicly listed — email us for business contact details.' }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-5">
            <a href="{{ route('support') }}" class="block rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
                <h2 class="text-lg font-extrabold text-brand-navy">Support centre</h2>
                <p class="mt-3 text-sm leading-6 text-brand-muted">Read help articles, common questions and contact instructions.</p>
                <p class="mt-4 text-sm font-extrabold text-brand-teal">Open support centre →</p>
            </a>
            <a href="{{ route('register') }}" class="block rounded-3xl border border-brand-teal/40 bg-brand-teal-soft p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
                <h2 class="text-lg font-extrabold text-brand-navy">Need an account?</h2>
                <p class="mt-3 text-sm leading-6 text-brand-muted">Create an account to place orders, manage your wallet and open support tickets.</p>
                <p class="mt-4 text-sm font-extrabold text-brand-teal">Create account →</p>
            </a>
        </div>

        <form method="POST" action="{{ route('contact.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <h2 class="text-2xl font-extrabold text-brand-navy">Send us a message</h2>
            <p class="mt-2 text-sm text-brand-muted">We use these details only to reply to your message.</p>

            @if($errors->any())
                <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                    Please check the form and try again.
                </div>
            @endif

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="text-sm font-bold text-brand-text">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal">
                    @error('name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="text-sm font-bold text-brand-text">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal">
                    @error('email')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="text-sm font-bold text-brand-text">Phone number <span class="font-semibold text-brand-muted">(optional)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal">
                    @error('phone')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="subject" class="text-sm font-bold text-brand-text">Subject</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal">
                    @error('subject')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="message" class="text-sm font-bold text-brand-text">Message</label>
                    <textarea id="message" name="message" rows="6" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-brand-teal focus:ring-brand-teal">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <button type="submit" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-brand-teal px-6 py-3 text-sm font-extrabold text-brand-navy transition hover:bg-brand-gold sm:w-auto">Send Message</button>
        </form>
    </div>
</section>
@endsection
