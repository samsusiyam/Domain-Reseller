@extends('layouts.guest', ['title' => 'TLD Wholesale Pricing Matrix'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14" x-data="{
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
    <div class="mb-8">
        <div class="cf-breadcrumb mb-2">
            <a href="{{ route('home') }}">Home</a>
            <span class="cf-breadcrumb-sep">/</span>
            <span class="cf-breadcrumb-current">Pricing</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">TLD wholesale pricing matrix</h1>
                <p class="text-sm mt-1" style="color: var(--text-muted);">
                    Direct registrar wholesale rates. Transparent renewals and automated multi-year provisioning.
                </p>
            </div>
            <div class="w-full sm:w-64">
                <input type="text"
                       x-model="search"
                       placeholder="Filter extension (e.g. .com)..."
                       class="cf-input h-9 text-xs font-mono">
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
                    All extensions
                </button>
                <button type="button"
                        @click="category = 'popular'"
                        class="cf-btn cf-btn-sm"
                        :class="category === 'popular' ? 'cf-btn-primary' : 'cf-btn-secondary'">
                    Popular (.com, .net, .org)
                </button>
                <button type="button"
                        @click="category = 'tech'"
                        class="cf-btn cf-btn-sm"
                        :class="category === 'tech' ? 'cf-btn-primary' : 'cf-btn-secondary'">
                    Tech (.io, .co)
                </button>
                <button type="button"
                        @click="category = 'cheap'"
                        class="cf-btn cf-btn-sm"
                        :class="category === 'cheap' ? 'cf-btn-primary' : 'cf-btn-secondary'">
                    Budget (.xyz)
                </button>
            </div>
        </div>
    </div>

    <!-- Pricing Table -->
    <div class="cf-table-wrapper">
        <table class="cf-table">
            <thead>
                <tr>
                    <th>TLD Extension</th>
                    <th>Registrar driver</th>
                    <th class="align-right">
                        <span x-text="viewTier === 'retail' ? 'Registration (1 yr)' : 'Wholesale Reg (1 yr)'"></span>
                    </th>
                    <th class="align-right">
                        <span x-text="viewTier === 'retail' ? 'Renewal (1 yr)' : 'Wholesale Renew (1 yr)'"></span>
                    </th>
                    <th class="align-right">Transfer</th>
                    <th>Included features</th>
                    <th class="align-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tlds as $tld)
                    <tr x-show="(!search || '{{ strtolower($tld->tld) }}'.includes(search.toLowerCase())) && matchesCategory('{{ strtolower($tld->tld) }}')">
                        <td class="font-mono font-semibold" style="color: var(--text);">
                            {{ $tld->tld }}
                            <span class="text-[11px] font-normal ml-1" style="color: var(--text-muted);">
                                ({{ $tld->min_years }}-{{ $tld->max_years }} yr)
                            </span>
                        </td>
                        <td class="text-xs" style="color: var(--text-muted);">
                            {{ $tld->registrar?->name ?? 'Tier 1' }}
                        </td>
                        <td class="align-right font-mono font-medium tabular-nums" style="color: var(--text);">
                            <template x-if="viewTier === 'retail'">
                                <x-price :usd="$tld->client_register" />
                            </template>
                            <template x-if="viewTier === 'reseller'">
                                <span class="font-bold text-[#F6821F]">
                                    <x-price :usd="$tld->default_reseller_register" />
                                </span>
                            </template>
                        </td>
                        <td class="align-right font-mono tabular-nums" style="color: var(--text-muted);">
                            <template x-if="viewTier === 'retail'">
                                <x-price :usd="$tld->client_renew" />
                            </template>
                            <template x-if="viewTier === 'reseller'">
                                <span class="font-bold text-[#F6821F]">
                                    <x-price :usd="$tld->default_reseller_renew" />
                                </span>
                            </template>
                        </td>
                        <td class="align-right font-mono tabular-nums" style="color: var(--text-muted);">
                            <template x-if="viewTier === 'retail'">
                                <x-price :usd="$tld->client_transfer" />
                            </template>
                            <template x-if="viewTier === 'reseller'">
                                <span class="font-bold text-[#F6821F]">
                                    <x-price :usd="$tld->default_reseller_transfer" />
                                </span>
                            </template>
                        </td>
                        <td class="text-xs" style="color: var(--text-muted);">
                            WHOIS privacy · DNS zone management
                        </td>
                        <td class="align-right">
                            <template x-if="viewTier === 'retail'">
                                <a href="{{ route('domain.search', ['query' => 'domain' . $tld->tld]) }}"
                                   class="cf-btn cf-btn-secondary cf-btn-sm">
                                    Register
                                </a>
                            </template>
                            <template x-if="viewTier === 'reseller'">
                                <a href="{{ route('register') }}?role=reseller"
                                   class="cf-btn cf-btn-primary cf-btn-sm font-semibold">
                                    Join Wholesale
                                </a>
                            </template>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center" style="color: var(--text-muted);">
                            No active domain pricing configured yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot style="background-color: var(--surface); border-top: 1px solid var(--border); color: var(--text-muted);">
                <tr>
                    <td colspan="7" class="px-5 py-3">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 w-full text-[12px]">
                            <span>Prices shown in USD. Convert instantly to BDT in the header currency switcher.</span>
                            <span>Direct API Provisioning Guaranteed</span>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>
@endsection
