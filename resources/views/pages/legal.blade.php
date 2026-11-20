@extends('layouts.site')

@section('title', $legal['title'].' | TrustVerifySMS')
@section('description', $legal['description'])

@section('content')
<section class="relative overflow-hidden bg-brand-navy text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #06B6D4 0, transparent 32%), radial-gradient(circle at 80% 10%, #F59E0B 0, transparent 24%);"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-teal">Legal</p>
        <h1 class="mt-3 max-w-3xl text-4xl font-extrabold sm:text-5xl">{{ $legal['title'] }}</h1>
        <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-300">{{ $legal['intro'] }}</p>
        <p class="mt-4 text-sm text-slate-400">Last updated: {{ $legal['updated'] }}</p>
    </div>
</section>

<section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="space-y-8">
        @foreach($legal['sections'] as $section)
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-extrabold text-brand-navy">{{ $section['heading'] }}</h2>
                @foreach($section['paragraphs'] ?? [] as $paragraph)
                    <p class="mt-4 text-sm leading-7 text-brand-muted">{{ $paragraph }}</p>
                @endforeach
                @if(! empty($section['list']))
                    <ul class="mt-4 space-y-3 text-sm leading-7 text-brand-muted">
                        @foreach($section['list'] as $item)
                            <li class="flex gap-3"><span class="font-extrabold text-brand-teal">•</span><span>{{ $item }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-10 rounded-3xl bg-brand-teal-soft p-8">
        <h2 class="text-xl font-extrabold text-brand-navy">Questions about this page?</h2>
        <p class="mt-2 text-sm leading-7 text-brand-text">Use the support centre for account, order or payment questions. For legal requests, include the page name and your account email in the ticket.</p>
        <a href="{{ route('support') }}" class="mt-5 inline-flex rounded-xl bg-brand-navy px-5 py-3 text-sm font-bold text-white hover:bg-brand-navy-light">Contact support</a>
    </div>
</section>
@endsection
