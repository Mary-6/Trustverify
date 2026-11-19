@extends('layouts.account')

@section('title', 'Virtual Numbers | Trustverifysms')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-brand-navy">Virtual numbers</h1>
        <p class="mt-1 text-brand-muted">Numbers purchased through supported products appear here with SMS messages when available.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($numbers->isEmpty())
            <p class="text-brand-muted">No virtual numbers yet. <a class="font-bold text-brand-teal" href="{{ route('services.index', ['type' => 'virtual_number']) }}">Browse number products</a>.</p>
        @else
            <div class="space-y-4">
                @foreach($numbers as $number)
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-lg font-extrabold text-brand-navy">{{ $number->phone_number }}</p>
                                <p class="text-sm text-brand-muted">{{ $number->country?->name }} • {{ $number->type }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full bg-brand-teal-soft px-3 py-1 text-xs font-bold text-brand-navy">{{ $number->status }}</span>
                                <span class="text-sm font-semibold">{{ number_format((float) $number->price, 0) }} {{ $number->currency }}</span>
                            </div>
                        </div>
                        @if($number->messages->isNotEmpty())
                            <div class="mt-4 rounded-xl bg-brand-bg p-4">
                                <h3 class="text-sm font-extrabold text-brand-navy">SMS messages</h3>
                                <ul class="mt-2 space-y-2 text-sm">
                                    @foreach($number->messages->take(5) as $message)
                                        <li class="rounded-lg bg-white px-3 py-2"><span class="font-semibold">{{ $message->from_address }}:</span> {{ Str::limit($message->body, 120) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-5">{{ $numbers->links() }}</div>
        @endif
    </div>
</div>
@endsection
