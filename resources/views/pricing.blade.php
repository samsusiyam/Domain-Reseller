@extends('layouts.guest', ['title' => 'TLD Wholesale Pricing Matrix'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" x-data="{
    search: '',
    category: 'all',
    viewTier: 'retail',
    matchesCategory(tld) {
        if (this.category === 'all') return true;
        if (this.category === 'popular') return ['.com', '.net', '.org', '.xyz'].includes(tld);
        if (this.category === 'tech') return ['.io', '.co', '.dev', '.tech'].includes(tld);
        if (this.category === 'cheap') return ['.xyz', '.co', '.online'].includes(tld);
        return true;
    }
}">

    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
        <div class="cf-breadcrumb mb-2 text-xs">
            <a href="{{ route('home') }}" class="text-slate-400 hover:text-slate-200">Home</a>
            <span class="cf-breadcrumb-sep text-slate-600">/</span>
            <span class="cf-breadcrumb-current text-[#F6821F] font-semibold">Pricing</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">TLD Wholesale Pricing Matrix</h1>
                <p class="text-xs sm:text-sm mt-1 text-slate-500 dark:text-slate-400">
                    Direct registrar wholesale rates. Transparent renewals and automated multi-year provisioning.
                </p>
            </div>
            <div class="w-full sm:w-64">
                <input type="text"
                       x-model="search"
                       placeholder="Filter extension (e.g. .com)..."
                       class="cf-input h-10 text-xs font-mono w-full">
            </div>
        </div>

        <!-- Tier Switcher & Category Filter -->
        <div class="mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4 pt-4 border-t border-slate-200 dark:border-slate-800">
            <!-- Tier Segmented Control -->
            <div class="inline-flex p-1 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60 text-xs">
                <button type="button" @click="viewTier = 'retail'"
                        :class="viewTier === 'retail' ? 'bg-white dark:bg-[#111827] text-slate-900 dark:text-white font-semibold shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-md transition-all cursor-pointer">
                    Retail Client Pricing
                </button>
                <button type="button" @click="viewTier = 'reseller'"
                        :class="viewTier === 'reseller' ? 'bg-[#F6821F] text-white font-semibold shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-md transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>Reseller Wholesale Tier</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-black/20 text-white font-mono uppercase">Wholesale</span>
                </button>
            </div>

            <!-- Category Filter Buttons -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <button type="button"
                        @click="category = 'all'"
                        class="cf-btn cf-btn-sm"
                        :class="category === 'all' ? 'cf-btn-primary' : 'cf-btn-secondary'">
                    All TLDs
                </button>
                <button type="button"
                        @click="category = 'popular'"
                        class="cf-btn cf-btn-sm"
                        :class="category === 'popular' ? 'cf-btn-primary' : 'cf-btn-secondary'">
                    Popular
                </button>
                <button type="button"
                        @click="category = 'tech'"
                        class="cf-btn cf-btn-sm"
                        :class="category === 'tech' ? 'cf-btn-primary' : 'cf-btn-secondary'">
                    Tech & Dev
                </button>
                <button type="button"
                        @click="category = 'cheap'"
                        class="cf-btn cf-btn-sm"
                        :class="category === 'cheap' ? 'cf-btn-primary' : 'cf-btn-secondary'">
                    Budget
                </button>
            </div>
        </div>
    </div>

    <!-- Pricing Table -->
    <div class="bg-white dark:bg-[#111622] rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-[#161D2E] border-b border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-3 sm:px-4">Extension</th>
                        <th class="py-3 px-3 sm:px-4 hidden md:table-cell">Registrar</th>
                        <th class="py-3 px-3 sm:px-4 text-right">
                            <span x-text="viewTier === 'retail' ? 'Register' : 'Wholesale Reg'"></span>
                        </th>
                        <th class="py-3 px-3 sm:px-4 text-right hidden sm:table-cell">
                            <span x-text="viewTier === 'retail' ? 'Renew' : 'Wholesale Renew'"></span>
                        </th>
                        <th class="py-3 px-3 sm:px-4 text-right hidden sm:table-cell">Transfer</th>
                        <th class="py-3 px-3 sm:px-4 hidden lg:table-cell">Included features</th>
                        <th class="py-3 px-3 sm:px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-800 dark:text-slate-200">
                    @forelse($tlds as $tld)
                        <tr x-show="(!search || '{{ strtolower($tld->tld) }}'.includes(search.toLowerCase())) && matchesCategory('{{ strtolower($tld->tld) }}')"
                            class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-3 sm:px-4 font-mono font-bold text-slate-900 dark:text-white">
                                {{ $tld->tld }}
                                <span class="text-[11px] font-normal text-slate-400 block sm:inline">
                                    ({{ $tld->min_years }}-{{ $tld->max_years }} yr)
                                </span>
                            </td>
                            <td class="py-3.5 px-3 sm:px-4 text-xs text-slate-400 hidden md:table-cell font-medium">
                                {{ $tld->registrar?->name ?? 'Tier 1' }}
                            </td>
                            <td class="py-3.5 px-3 sm:px-4 text-right font-mono font-bold text-[#F6821F] tabular-nums">
                                <template x-if="viewTier === 'retail'">
                                    <x-price :usd="$tld->client_register" />
                                </template>
                                <template x-if="viewTier === 'reseller'">
                                    <span class="font-bold text-[#F6821F]">
                                        <x-price :usd="$tld->default_reseller_register" />
                                    </span>
                                </template>
                            </td>
                            <td class="py-3.5 px-3 sm:px-4 text-right font-mono tabular-nums text-slate-400 hidden sm:table-cell">
                                <template x-if="viewTier === 'retail'">
                                    <x-price :usd="$tld->client_renew" />
                                </template>
                                <template x-if="viewTier === 'reseller'">
                                    <span class="font-bold text-[#F6821F]">
                                        <x-price :usd="$tld->default_reseller_renew" />
                                    </span>
                                </template>
                            </td>
                            <td class="py-3.5 px-3 sm:px-4 text-right font-mono tabular-nums text-slate-400 hidden sm:table-cell">
                                <template x-if="viewTier === 'retail'">
                                    <x-price :usd="$tld->client_transfer" />
                                </template>
                                <template x-if="viewTier === 'reseller'">
                                    <span class="font-bold text-[#F6821F]">
                                        <x-price :usd="$tld->default_reseller_transfer" />
                                    </span>
                                </template>
                            </td>
                            <td class="py-3.5 px-3 sm:px-4 text-xs text-slate-400 hidden lg:table-cell">
                                Free WHOIS privacy · DNS Zone
                            </td>
                            <td class="py-3.5 px-3 sm:px-4 text-right">
                                <template x-if="viewTier === 'retail'">
                                    <a href="{{ route('domain.search', ['query' => 'domain' . $tld->tld]) }}"
                                       class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-semibold">
                                        Register
                                    </a>
                                </template>
                                <template x-if="viewTier === 'reseller'">
                                    <a href="{{ route('register') }}?role=reseller"
                                       class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold">
                                        Wholesale
                                    </a>
                                </template>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                No active domain pricing configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/70 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400">
                    <tr>
                        <td colspan="7" class="py-3 px-3 sm:px-4">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                                <span>Prices shown in USD. Convert instantly to BDT in the header currency switcher.</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Direct Upstream Registry Routing</span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
