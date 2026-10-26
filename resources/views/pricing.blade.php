@extends('layouts.guest')

@section('content')
<div class="py-12 bg-white" x-data="{ search: '' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-10">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">TLD Pricing Table</h1>
            <p class="mt-3 text-base text-slate-500">
                Transparent domain pricing with no hidden renewal markups. All prices available in both USD and live-converted BDT.
            </p>

            <!-- Search Filter -->
            <div class="mt-6 max-w-md mx-auto">
                <input type="text" x-model="search" placeholder="Filter by extension (e.g. .com, .net, .xyz)..."
                    class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
        </div>

        <!-- Pricing Table -->
        <div class="overflow-hidden border border-slate-200 rounded-2xl shadow-sm bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-6">Extension</th>
                        <th class="py-3.5 px-6">New Registration</th>
                        <th class="py-3.5 px-6">Renewal</th>
                        <th class="py-3.5 px-6">Transfer</th>
                        <th class="py-3.5 px-6 text-center">ID Protection</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                    @forelse($tlds as $tld)
                        <tr x-show="!search || '{{ strtolower($tld->tld) }}'.includes(search.toLowerCase())" class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6 font-mono font-bold text-indigo-600 text-base">
                                {{ $tld->tld }}
                            </td>
                            <td class="py-4 px-6">
                                <x-price-display :amount="$tld->client_register" />
                            </td>
                            <td class="py-4 px-6">
                                <x-price-display :amount="$tld->client_renew" />
                            </td>
                            <td class="py-4 px-6">
                                <x-price-display :amount="$tld->client_transfer" />
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($tld->id_protection_available)
                                    @if((float)$tld->id_protection_cost == 0)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700">Free</span>
                                    @else
                                        <span class="text-xs text-slate-600">${{ number_format($tld->id_protection_cost, 2) }}/yr</span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('domain.search') }}?tld={{ urlencode($tld->tld) }}" class="inline-flex items-center px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs rounded-lg transition">
                                    Search
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                No TLDs currently configured.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
