@extends('layouts.admin')

@section('title', 'Countries | Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-brand-navy">Countries</h1>
            <p class="mt-1 text-brand-muted">Coverage used for virtual numbers and destination filters.</p>
        </div>
        <a href="{{ route('admin.countries.create') }}" class="rounded-xl bg-brand-teal px-5 py-3 text-sm font-extrabold text-brand-navy hover:bg-brand-gold">Add country</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-brand-bg text-left text-xs uppercase text-brand-muted">
                <tr><th class="px-5 py-3">Country</th><th class="px-5 py-3">Code</th><th class="px-5 py-3">Dial</th><th class="px-5 py-3">Currency</th><th class="px-5 py-3">Sort</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($countries as $country)
                    <tr class="border-t border-slate-100">
                        <td class="px-5 py-4 font-bold text-brand-navy">{{ $country->name }}</td>
                        <td class="px-5 py-4">{{ $country->code }}</td>
                        <td class="px-5 py-4">{{ $country->dial_code ?: '—' }}</td>
                        <td class="px-5 py-4">{{ $country->currency ?: '—' }}</td>
                        <td class="px-5 py-4">{{ $country->sort_order }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-bold {{ $country->is_active ? 'bg-brand-teal-soft text-brand-navy' : 'bg-slate-100 text-brand-muted' }}">{{ $country->is_active ? 'active' : 'inactive' }}</span></td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.countries.edit', $country) }}" class="font-bold text-brand-teal hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.countries.destroy', $country) }}" class="mt-2 inline" onsubmit="return confirm('Delete this country?')">@csrf @method('DELETE')<button class="font-bold text-brand-error hover:underline">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-brand-muted">No countries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $countries->links() }}
</div>
@endsection
