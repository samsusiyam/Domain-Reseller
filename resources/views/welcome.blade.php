@extends('layouts.guest')

@section('content')
<div class="space-y-16 sm:space-y-24 py-8 sm:py-12">

    @php
        $dotCom = $popularTlds->firstWhere('tld', '.com');
        $dotNet = $popularTlds->firstWhere('tld', '.net');
        $dotOrg = $popularTlds->firstWhere('tld', '.org');
        $dotXyz = $popularTlds->firstWhere('tld', '.xyz');
        $dotIo  = $popularTlds->firstWhere('tld', '.io');

        $comPrice = $dotCom ? (float) $dotCom->client_register : 12.99;
        $netPrice = $dotNet ? (float) $dotNet->client_register : 14.99;
        $xyzPrice = $dotXyz ? (float) $dotXyz->client_register : 3.49;
    @endphp

    <!-- 1. HERO SECTION (Left-aligned, 2-column, 48px H1, 56px search, real preview card) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

            <!-- Left Column: Heading, Subtitle, 56px Search Bar, Plain Text TLD Prices -->
            <div class="lg:col-span-7 space-y-6 text-left">

                <h1 class="text-[36px] sm:text-[44px] lg:text-[48px] font-semibold tracking-tight leading-[1.1] max-w-xl" style="color: var(--text);">
                    Register domains at wholesale prices. Pay in BDT or USD.
                </h1>

                <p class="text-[15px] sm:text-[16px] max-w-lg leading-relaxed" style="color: var(--text-muted);">
                    Direct upstream connections to Namecheap, Spaceship, Dynadot, Cosmotown, and NameSilo with automated DNS and WHMCS integration.
                </p>

                <!-- Search Bar Container (56px) -->
                <div class="pt-2 max-w-xl">
                    <form action="{{ route('domain.search') }}" method="GET" class="relative">
                        <div class="relative flex items-center h-14 rounded-[6px] transition border"
                             style="background-color: var(--bg); border-color: var(--border);">
                            <svg class="w-5 h-5 ml-4 absolute pointer-events-none" style="color: var(--text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <input type="text"
                                   name="query"
                                   placeholder="Find your domain name (e.g. mycompany.com)..."
                                   required
                                   class="w-full h-full pl-12 pr-28 text-[14px] sm:text-[15px] bg-transparent border-0 focus:outline-none"
                                   style="color: var(--text);">
                            <button type="submit"
                                    class="cf-btn cf-btn-primary absolute right-1.5 top-1.5 bottom-1.5 px-5 text-[14px]">
                                Search
                            </button>
                        </div>
                    </form>

                    <!-- Under Search: Popular TLD prices as single clean line or 2-col list on small screens -->
                    <div class="mt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[12px]" style="color: var(--text-muted);">
                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-x-3 gap-y-1">
                            @foreach($popularTlds->take(5) as $tld)
                                <div class="flex items-center gap-1">
                                    <strong class="font-medium" style="color: var(--text);">{{ $tld->tld }}</strong>
                                    <span>from <x-price :usd="$tld->client_register" />/yr</span>
                                    @if(!$loop->last)
                                        <span class="hidden sm:inline" style="color: var(--border);">·</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Bulk search link -->
                        <a href="{{ route('domain.search') }}" class="font-medium shrink-0 hover:underline" style="color: var(--accent);">
                            Search multiple &rarr;
                        </a>
                    </div>
                </div>

            </div>

            <!-- Right Column: Real-looking, clean search result preview card -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end">
                <div class="w-full max-w-md cf-panel p-5 space-y-4">

                    <!-- Preview Header -->
                    <div class="flex items-center justify-between pb-3 border-b" style="border-color: var(--border);">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-mono uppercase font-medium" style="color: var(--text-muted);">Domain Availability</span>
                        </div>
                        <span class="text-[11px]" style="color: var(--text-muted);">Live registry node</span>
                    </div>

                    <!-- Main Highlight Domain Card (.com reading exact catalog price) -->
                    <div class="p-4 rounded-[6px] border space-y-3" style="background-color: var(--surface); border-color: var(--border);">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-semibold text-[16px]" style="color: var(--text);">examplebrand.com</span>
                            <div class="cf-status-dot success">
                                <span>Available</span>
                            </div>
                        </div>

                        <div class="flex items-baseline justify-between pt-1">
                            <div>
                                <span class="font-mono font-semibold text-[18px] tabular-nums" style="color: var(--text);">
                                    <x-price :usd="$comPrice" />
                                </span>
                                <span class="text-[12px]" style="color: var(--text-muted);">/year</span>
                            </div>

                            <a href="{{ route('domain.search', ['query' => 'examplebrand.com']) }}"
                               class="cf-btn cf-btn-primary cf-btn-sm">
                                Add to cart
                            </a>
                        </div>
                    </div>

                    <!-- Secondary Extensions List -->
                    <div class="divide-y text-[13px]" style="border-color: var(--border);">
                        <!-- Row 1: .net -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="cf-status-dot success"></span>
                                <span class="font-mono" style="color: var(--text);">examplebrand.net</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-[13px] tabular-nums" style="color: var(--text);"><x-price :usd="$netPrice" /></span>
                                <a href="{{ route('domain.search', ['query' => 'examplebrand.net']) }}"
                                   class="cf-btn cf-btn-secondary cf-btn-sm text-[12px]">
                                    Add
                                </a>
                            </div>
                        </div>

                        <!-- Row 2: .org (Taken) -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="cf-status-dot neutral"></span>
                                <span class="font-mono" style="color: var(--text-muted);">examplebrand.org</span>
                            </div>
                            <span class="text-[12px]" style="color: var(--text-muted);">Taken</span>
                        </div>

                        <!-- Row 3: .xyz -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="cf-status-dot success"></span>
                                <span class="font-mono" style="color: var(--text);">examplebrand.xyz</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-[13px] tabular-nums" style="color: var(--text);"><x-price :usd="$xyzPrice" /></span>
                                <a href="{{ route('domain.search', ['query' => 'examplebrand.xyz']) }}"
                                   class="cf-btn cf-btn-secondary cf-btn-sm text-[12px]">
                                    Add
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer Checkmarks -->
                    <div class="pt-2 border-t text-[11px] flex items-center justify-between" style="border-color: var(--border); color: var(--text-muted);">
                        <span>Includes WHOIS privacy</span>
                        <span>Free DNS hosting</span>
                        <span>EPP code unlock</span>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- 2. LOGOS STRIP: Connected upstream registries -->
    <section class="border-y py-3.5 bg-slate-50/75 dark:bg-[#12151C]/75 border-slate-200 dark:border-slate-800 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3 text-[12px]">
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 font-medium shrink-0 uppercase tracking-wider text-[11px]">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Upstream Registries:</span>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 text-slate-600 dark:text-slate-300 font-mono text-[12px] font-medium">
                    <span class="px-2.5 py-1 rounded-md bg-white dark:bg-[#1A1E26] border border-slate-200 dark:border-slate-800 shadow-xs">Namecheap</span>
                    <span class="px-2.5 py-1 rounded-md bg-white dark:bg-[#1A1E26] border border-slate-200 dark:border-slate-800 shadow-xs">Spaceship</span>
                    <span class="px-2.5 py-1 rounded-md bg-white dark:bg-[#1A1E26] border border-slate-200 dark:border-slate-800 shadow-xs">Dynadot</span>
                    <span class="px-2.5 py-1 rounded-md bg-white dark:bg-[#1A1E26] border border-slate-200 dark:border-slate-800 shadow-xs">Cosmotown</span>
                    <span class="px-2.5 py-1 rounded-md bg-white dark:bg-[#1A1E26] border border-slate-200 dark:border-slate-800 shadow-xs">AtakDomain</span>
                    <span class="px-2.5 py-1 rounded-md bg-white dark:bg-[#1A1E26] border border-slate-200 dark:border-slate-800 shadow-xs">NameSilo</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. PRICING TABLE SECTION: "Popular extensions" (Single source of truth) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <h2 class="text-[20px] font-semibold leading-tight" style="color: var(--text);">Popular extensions</h2>
                    <p class="text-[13px] mt-0.5" style="color: var(--text-muted);">Wholesale pricing across top domain extensions with transparent renewal rates.</p>
                </div>
                <a href="{{ route('pricing') }}" class="text-[13px] hover:underline font-medium inline-flex items-center gap-1 shrink-0" style="color: var(--accent);">
                    See all extensions &rarr;
                </a>
            </div>

            <!-- Clean Table Container -->
            <div class="cf-table-wrapper">
                <table class="cf-table">
                    <thead>
                        <tr>
                            <th>TLD</th>
                            <th class="align-right">Register</th>
                            <th class="align-right">Renew</th>
                            <th class="align-right">Transfer</th>
                            <th>Included features</th>
                            <th class="align-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($popularTlds as $tld)
                            <tr>
                                <!-- TLD -->
                                <td class="font-mono font-semibold" style="color: var(--text);">
                                    {{ $tld->tld }}
                                </td>

                                <!-- Register -->
                                <td class="align-right font-mono font-medium tabular-nums" style="color: var(--text);">
                                    <x-price :usd="$tld->client_register" />
                                </td>

                                <!-- Renew -->
                                <td class="align-right font-mono tabular-nums" style="color: var(--text-muted);">
                                    <x-price :usd="$tld->client_renew" />
                                </td>

                                <!-- Transfer -->
                                <td class="align-right font-mono tabular-nums" style="color: var(--text-muted);">
                                    <x-price :usd="$tld->client_transfer" />
                                </td>

                                <!-- Features -->
                                <td class="text-[12px]" style="color: var(--text-muted);">
                                    Free WHOIS privacy · DNS management
                                </td>

                                <!-- Action -->
                                <td class="align-right">
                                    <a href="{{ route('domain.search', ['query' => 'domain' . $tld->tld]) }}"
                                       class="cf-btn cf-btn-secondary cf-btn-sm">
                                        Register
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center" style="color: var(--text-muted);">
                                    No active TLD pricing configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot style="background-color: var(--surface); border-top: 1px solid var(--border); color: var(--text-muted);">
                        <tr>
                            <td colspan="6" class="px-5 py-3">
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 w-full text-[12px]">
                                    <span>Prices shown in USD. Instant conversion to BDT during checkout.</span>
                                    <a href="{{ route('pricing') }}" class="font-medium hover:underline" style="color: var(--accent);">
                                        View complete pricing matrix &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>

</div>
@endsection
