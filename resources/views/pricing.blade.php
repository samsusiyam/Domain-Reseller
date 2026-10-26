@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto" x-data="{
    search: '',
    actionFilter: 'all',
    category: 'all',
    matchesCategory(tld) {
        if (this.category === 'all') return true;
        if (this.category === 'popular') return ['.com', '.net', '.org', '.xyz'].includes(tld);
        if (this.category === 'tech') return ['.io', '.co', '.dev', '.tech'].includes(tld);
        if (this.category === 'cheap') return ['.xyz', '.co', '.top'].includes(tld);
        return true;
    }
}">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-12">
        <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
            Transparent Registry Pricing
        </span>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mt-4">
            TLD Wholesale Pricing Table
        </h1>
        <p class="mt-3 text-base text-slate-400">
            No hidden renewal markups or price hikes. Complete register, renew, and transfer rates in USD ($) and live BDT (৳).
        </p>

        <!-- Search & Filter Controls -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 max-w-xl mx-auto">
            <div class="relative w-full">
                <svg class="w-5 h-5 text-slate-500 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" x-model="search" placeholder="Search extension (e.g. .com, .io, .xyz)..."
                    class="w-full pl-12 pr-4 py-3 bg-obsidian-900 border border-white/15 rounded-2xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 font-mono">
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs">
            <button type="button" @click="category = 'all'"
                :class="category === 'all' ? 'bg-indigo-600 text-white' : 'bg-white/5 text-slate-400 hover:text-white'"
                class="px-3.5 py-1.5 rounded-xl font-bold transition">All TLDs</button>
            <button type="button" @click="category = 'popular'"
                :class="category === 'popular' ? 'bg-indigo-600 text-white' : 'bg-white/5 text-slate-400 hover:text-white'"
                class="px-3.5 py-1.5 rounded-xl font-bold transition">Popular (.com, .net, .org)</button>
            <button type="button" @click="category = 'tech'"
                :class="category === 'tech' ? 'bg-indigo-600 text-white' : 'bg-white/5 text-slate-400 hover:text-white'"
                class="px-3.5 py-1.5 rounded-xl font-bold transition">Tech (.io, .co)</button>
            <button type="button" @click="category = 'cheap'"
                :class="category === 'cheap' ? 'bg-indigo-600 text-white' : 'bg-white/5 text-slate-400 hover:text-white'"
                class="px-3.5 py-1.5 rounded-xl font-bold transition">Affordable (.xyz from $2.49)</button>
        </div>
    </div>

    <!-- Pricing Table Card -->
    <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm divide-y divide-white/10">
                <thead class="bg-obsidian-900/80 text-slate-300 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6 pl-8">Extension</th>
                        <th class="py-4 px-6">Registration</th>
                        <th class="py-4 px-6">Renewal</th>
                        <th class="py-4 px-6">Transfer</th>
                        <th class="py-4 px-6 text-center">WHOIS Privacy</th>
                        <th class="py-4 px-6 text-center">DNS & Email</th>
                        <th class="py-4 px-6 pr-8 text-right">Register</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-200">
                    @forelse($tlds as $tld)
                        @php
                            $rate = config('currencies.fallback_usd_to_bdt', 120.00);
                            $regBdt = (float)$tld->client_register * $rate;
                            $renewBdt = (float)$tld->client_renew * $rate;
                            $transferBdt = (float)$tld->client_transfer * $rate;
                        @endphp
                        <tr x-show="(!search || '{{ strtolower($tld->tld) }}'.includes(search.toLowerCase())) && matchesCategory('{{ strtolower($tld->tld) }}')"
                            class="hover:bg-white/5 transition">
                            <!-- TLD -->
                            <td class="py-4 px-6 pl-8">
                                <span class="font-mono font-black text-xl text-white block">{{ $tld->tld }}</span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $tld->min_years }} - {{ $tld->max_years }} Year(s)</span>
                            </td>

                            <!-- Registration Price -->
                            <td class="py-4 px-6">
                                <div class="font-mono font-bold text-base text-emerald-400">
                                    ${{ number_format((float)$tld->client_register, 2) }}
                                </div>
                                <div class="text-[11px] font-mono text-slate-400">
                                    ~ ৳{{ number_format($regBdt, 2) }}
                                </div>
                            </td>

                            <!-- Renewal Price -->
                            <td class="py-4 px-6">
                                <div class="font-mono font-semibold text-sm text-slate-200">
                                    ${{ number_format((float)$tld->client_renew, 2) }}
                                </div>
                                <div class="text-[11px] font-mono text-slate-400">
                                    ~ ৳{{ number_format($renewBdt, 2) }}
                                </div>
                            </td>

                            <!-- Transfer Price -->
                            <td class="py-4 px-6">
                                <div class="font-mono font-semibold text-sm text-slate-200">
                                    ${{ number_format((float)$tld->client_transfer, 2) }}
                                </div>
                                <div class="text-[11px] font-mono text-slate-400">
                                    ~ ৳{{ number_format($transferBdt, 2) }}
                                </div>
                            </td>

                            <!-- ID Protection -->
                            <td class="py-4 px-6 text-center">
                                @if($tld->id_protection_available)
                                    @if((float)$tld->id_protection_cost == 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            ✓ Free Always
                                        </span>
                                    @else
                                        <span class="font-mono text-xs text-slate-300">
                                            ${{ number_format((float)$tld->id_protection_cost, 2) }}/yr
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-500 font-mono">—</span>
                                @endif
                            </td>

                            <!-- DNS & Email Forwarding -->
                            <td class="py-4 px-6 text-center text-xs">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $tld->dns_management_available ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-800 text-slate-500' }}">
                                        DNS
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $tld->email_forwarding_available ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-800 text-slate-500' }}">
                                        Email
                                    </span>
                                </div>
                            </td>

                            <!-- Action Button -->
                            <td class="py-4 px-6 pr-8 text-right">
                                <a href="{{ route('domain.search') }}?tld={{ $tld->tld }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white shadow-md transition hover:scale-[1.02]">
                                    <span>Register</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                No active TLD pricing found in registry.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Registry Features Footer Callout -->
    <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
        <div class="p-6 rounded-2xl glass-card border border-white/10">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto mb-3 font-bold">
                ✓
            </div>
            <h4 class="font-bold text-white text-sm">Automated Real-Time Provisioning</h4>
            <p class="text-xs text-slate-400 mt-1">Domains are registered instantaneously through upstream APIs with zero manual delay.</p>
        </div>
        <div class="p-6 rounded-2xl glass-card border border-white/10">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto mb-3 font-bold">
                🔒
            </div>
            <h4 class="font-bold text-white text-sm">Free Privacy & DNS Included</h4>
            <p class="text-xs text-slate-400 mt-1">WHOIS identity masking and DNS management available on all supported extensions.</p>
        </div>
        <div class="p-6 rounded-2xl glass-card border border-white/10">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mx-auto mb-3 font-bold">
                ⚡
            </div>
            <h4 class="font-bold text-white text-sm">Reseller Volume Discounts</h4>
            <p class="text-xs text-slate-400 mt-1">Higher volume resellers enjoy automated tier upgrades with deeper wholesale margins.</p>
        </div>
    </div>
</div>
@endsection
