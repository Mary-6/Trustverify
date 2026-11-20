@extends('layouts.site')

@section('title', 'API Documentation | TrustVerifySMS')
@section('description', 'TrustVerifySMS customer API documentation for implemented endpoints only.')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Developer API</p>
    <h1 class="mt-2 text-4xl font-extrabold text-brand-navy">API access for implemented customer endpoints.</h1>
    <p class="mt-4 max-w-3xl text-brand-muted">Generate an API key from your dashboard. Keys are hashed at rest, scoped to implemented abilities, and can be revoked at any time. Only documented endpoints are supported.</p>
    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">Authentication</h2>
            <p class="mt-2 text-sm text-brand-muted">Send your key in the <code>Authorization: Bearer</code> header. Never expose keys in frontend JavaScript or public repositories.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-extrabold text-brand-navy">Available abilities</h2>
            <p class="mt-2 text-sm text-brand-muted">Default keys include <code>products:read</code> and <code>orders:read</code>. Additional abilities are enabled only when endpoints are implemented.</p>
        </div>
    </div>
    <div class="mt-8 rounded-2xl bg-brand-navy p-6 text-white">
        <h2 class="font-extrabold">Implemented endpoints</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-slate-400"><th class="pb-3">Method</th><th class="pb-3">Endpoint</th><th class="pb-3">Ability</th></tr></thead>
                <tbody>
                    <tr class="border-t border-white/10"><td class="py-3 font-bold text-brand-teal">GET</td><td class="py-3">/api/v1/products</td><td class="py-3">products:read</td></tr>
                    <tr class="border-t border-white/10"><td class="py-3 font-bold text-brand-teal">GET</td><td class="py-3">/api/v1/products/{id}</td><td class="py-3">products:read</td></tr>
                    <tr class="border-t border-white/10"><td class="py-3 font-bold text-brand-teal">GET</td><td class="py-3">/api/v1/orders</td><td class="py-3">orders:read</td></tr>
                    <tr class="border-t border-white/10"><td class="py-3 font-bold text-brand-teal">GET</td><td class="py-3">/api/v1/orders/{id}</td><td class="py-3">orders:read</td></tr>
                    <tr class="border-t border-white/10"><td class="py-3 font-bold text-brand-teal">GET</td><td class="py-3">/api/v1/wallet</td><td class="py-3">wallet:read</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-sm text-slate-300">Order creation and supplier-specific endpoints are exposed only after the corresponding supplier adapter is configured and tested.</p>
    </div>
</section>
@endsection
