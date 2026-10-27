@extends('layouts.guest', ['title' => 'TLD Wholesale Pricing Matrix'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14" x-data="{
    search: '',
    category: 'all',
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

        <!-- Category Segmented Filter -->
        <div class="mt-4 flex flex-wrap items-center gap-1.5 text-xs">
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

    <!-- Pricing Table -->
    <div class="cf-table-wrapper">
        <table class="cf-table">
            <thead>
                <tr>
                    <th>TLD Extension</th>
                    <th>Registrar driver</th>
                    <th class="align-right">Registration (1 yr)</th>
                    <th class="align-right">Renewal (1 yr)</th>
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
                            <x-price :usd="$tld->client_register" />
                        </td>
                        <td class="align-right font-mono tabular-nums" style="color: var(--text-muted);">
                            <x-price :usd="$tld->client_renew" />
                        </td>
                        <td class="align-right font-mono tabular-nums" style="color: var(--text-muted);">
                            <x-price :usd="$tld->client_transfer" />
                        </td>
                        <td class="text-xs" style="color: var(--text-muted);">
                            WHOIS privacy · DNS zone management
                        </td>
                        <td class="align-right">
                            <a href="{{ route('domain.search', ['query' => 'domain' . $tld->tld]) }}"
                               class="cf-btn cf-btn-secondary cf-btn-sm">
                                Register
                            </a>
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
