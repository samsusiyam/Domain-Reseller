@extends('layouts.guest')

@section('content')
<div class="space-y-12 sm:space-y-20 py-6 sm:py-12">

    @php
        $dotCom = $popularTlds->firstWhere('tld', '.com');
        $dotNet = $popularTlds->firstWhere('tld', '.net');
        $dotOrg = $popularTlds->firstWhere('tld', '.org');
        $dotXyz = $popularTlds->firstWhere('tld', '.xyz');
        $dotIo  = $popularTlds->firstWhere('tld', '.io');

        $comPrice = $dotCom ? (float) $dotCom->client_register : 10.64;
        $netPrice = $dotNet ? (float) $dotNet->client_register : 12.36;
        $xyzPrice = $dotXyz ? (float) $dotXyz->client_register : 2.13;
    @endphp

    <!-- 1. HERO SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <!-- Left Column -->
            <div class="lg:col-span-7 space-y-5 text-left">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-50 dark:bg-orange-950/40 text-[#F6821F] border border-orange-200 dark:border-orange-900/40">
                    <span class="w-2 h-2 rounded-full bg-[#F6821F] animate-pulse"></span>
                    <span>Direct Upstream Registrar Routing</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-[46px] font-bold tracking-tight leading-[1.15] text-slate-900 dark:text-white">
                    Register domains at wholesale prices. Pay in BDT or USD.
                </h1>

                <p class="text-sm sm:text-base leading-relaxed text-slate-600 dark:text-slate-400 max-w-xl">
                    Direct connections to Namecheap, Spaceship, Dynadot, Cosmotown, and NameSilo with instant multi-registrar failover, automated DNS, and turnkey WHMCS integration.
                </p>

                <!-- Search Bar Container -->
                <div class="pt-2 max-w-xl">
                    <form action="{{ route('domain.search') }}" method="GET" class="relative">
                        <div class="relative flex items-center h-12 sm:h-14 rounded-lg transition border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#111622] shadow-sm focus-within:border-[#F6821F] focus-within:ring-2 focus-within:ring-[#F6821F]/20">
                            <svg class="w-5 h-5 ml-3 sm:ml-4 text-slate-400 shrink-0 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <input type="text"
                                   name="query"
                                   placeholder="Search your domain name (e.g. brand.com)..."
                                   required
                                   class="w-full h-full pl-3 pr-24 sm:pr-28 text-sm sm:text-base bg-transparent border-0 focus:outline-none text-slate-900 dark:text-white placeholder:text-slate-400">
                            <button type="submit"
                                    class="cf-btn cf-btn-primary absolute right-1.5 top-1.5 bottom-1.5 px-4 sm:px-6 text-xs sm:text-sm font-semibold">
                                Search
                            </button>
                        </div>
                    </form>

                    <!-- Popular TLD Quick Links -->
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            @foreach($popularTlds->take(5) as $tld)
                                <a href="{{ route('domain.search', ['query' => 'domain' . $tld->tld]) }}" class="inline-flex items-center gap-1 hover:text-[#F6821F] transition">
                                    <strong class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $tld->tld }}</strong>
                                    <span>from <x-price :usd="$tld->client_register" /></span>
                                    @if(!$loop->last)
                                        <span class="text-slate-300 dark:text-slate-700 ml-1.5">·</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>

                        <a href="{{ route('domain.search') }}" class="font-medium text-[#F6821F] hover:underline inline-flex items-center gap-0.5">
                            <span>Search multiple</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Availability Demo Card -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end">
                <div class="w-full max-w-md bg-white dark:bg-[#111622] rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-4">

                    <!-- Preview Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-mono uppercase font-bold tracking-wider text-slate-400">Domain Availability</span>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Live registry node</span>
                        </span>
                    </div>

                    <!-- Main Highlight Domain Card -->
                    <div class="p-4 rounded-lg bg-slate-50 dark:bg-[#161D2E] border border-slate-200 dark:border-slate-700/60 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-base text-slate-900 dark:text-white">examplebrand.com</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400">
                                Available
                            </span>
                        </div>

                        <div class="flex items-baseline justify-between pt-1">
                            <div>
                                <span class="font-mono font-bold text-lg text-[#F6821F] tabular-nums">
                                    <x-price :usd="$comPrice" />
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">/year</span>
                            </div>

                            <a href="{{ route('domain.search', ['query' => 'examplebrand.com']) }}"
                               class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold">
                                Add to cart
                            </a>
                        </div>
                    </div>

                    <!-- Secondary Extensions List -->
                    <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                        <!-- Row 1: .net -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="font-mono text-slate-800 dark:text-slate-200 font-medium">examplebrand.net</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono font-semibold tabular-nums text-slate-900 dark:text-white"><x-price :usd="$netPrice" /></span>
                                <a href="{{ route('domain.search', ['query' => 'examplebrand.net']) }}"
                                   class="cf-btn cf-btn-secondary cf-btn-sm text-[11px]">
                                    Add
                                </a>
                            </div>
                        </div>

                        <!-- Row 2: .org (Taken) -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                <span class="font-mono text-slate-400">examplebrand.org</span>
                            </div>
                            <span class="text-[11px] font-medium text-slate-400">Taken</span>
                        </div>

                        <!-- Row 3: .xyz -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="font-mono text-slate-800 dark:text-slate-200 font-medium">examplebrand.xyz</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono font-semibold tabular-nums text-slate-900 dark:text-white"><x-price :usd="$xyzPrice" /></span>
                                <a href="{{ route('domain.search', ['query' => 'examplebrand.xyz']) }}"
                                   class="cf-btn cf-btn-secondary cf-btn-sm text-[11px]">
                                    Add
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                        <span>✓ WHOIS privacy</span>
                        <span>✓ Free DNS</span>
                        <span>✓ EPP unlock</span>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- 2. REGISTRIES LOGOS STRIP -->
    <section class="border-y border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/40 py-3.5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Connected Registrars:</span>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-2.5 font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <span class="px-2.5 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">Namecheap</span>
                    <span class="px-2.5 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">Spaceship</span>
                    <span class="px-2.5 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">Dynadot</span>
                    <span class="px-2.5 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">Cosmotown</span>
                    <span class="px-2.5 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">AtakDomain</span>
                    <span class="px-2.5 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">NameSilo</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. POPULAR EXTENSIONS TABLE -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Popular Extensions & Wholesale Rates</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Transparent registration, renewal, and transfer rates across all major extensions.</p>
                </div>
                <a href="{{ route('pricing') }}" class="text-xs font-semibold text-[#F6821F] hover:underline inline-flex items-center gap-1">
                    <span>View all extensions &rarr;</span>
                </a>
            </div>

            <!-- Responsive Table Container -->
            <div class="bg-white dark:bg-[#111622] rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-[#161D2E] border-b border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-2.5 sm:px-4 sm:px-6">Extension</th>
                                <th class="py-3 px-2.5 sm:px-4 text-right">Register</th>
                                <th class="py-3 px-2 sm:px-4 text-right hidden sm:table-cell">Renew</th>
                                <th class="py-3 px-2.5 sm:px-4 text-right hidden sm:table-cell">Transfer</th>
                                <th class="py-3 px-2.5 sm:px-4 hidden md:table-cell">Features</th>
                                <th class="py-3 px-2.5 sm:px-4 sm:px-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-800 dark:text-slate-200">
                            @forelse($popularTlds as $tld)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-2.5 sm:px-4 sm:px-6 font-mono font-bold text-slate-900 dark:text-white">
                                        {{ $tld->tld }}
                                    </td>
                                    <td class="py-3.5 px-2.5 sm:px-4 text-right font-mono font-bold text-[#F6821F] tabular-nums">
                                        <x-price :usd="$tld->client_register" />
                                    </td>
                                    <td class="py-3.5 px-2.5 sm:px-4 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400 hidden sm:table-cell">
                                        <x-price :usd="$tld->client_renew" />
                                    </td>
                                    <td class="py-3.5 px-2.5 sm:px-4 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400 hidden sm:table-cell">
                                        <x-price :usd="$tld->client_transfer" />
                                    </td>
                                    <td class="py-3.5 px-2.5 sm:px-4 text-xs text-slate-500 dark:text-slate-400 hidden md:table-cell">
                                        Free WHOIS privacy · DNS Zone
                                    </td>
                                    <td class="py-3.5 px-2.5 sm:px-4 sm:px-6 text-right">
                                        <a href="{{ route('domain.search', ['query' => 'domain' . $tld->tld]) }}"
                                           class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-semibold">
                                            Register
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">No active extensions available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50/70 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400">
                            <tr>
                                <td colspan="6" class="py-3 px-2.5 sm:px-4 sm:px-6">
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                                        <span>All prices listed in USD. Real-time conversion to BDT during checkout.</span>
                                        <a href="{{ route('pricing') }}" class="font-semibold text-[#F6821F] hover:underline">
                                            View complete pricing matrix &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. KEY PLATFORM CAPABILITIES (3 CARDS) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1 -->
            <div class="p-6 rounded-xl bg-white dark:bg-[#111622] border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-950/40 text-[#F6821F] flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Multi-Registrar Failover</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    If an upstream registrar experiences latency or network maintenance, our engine automatically routes your registration to the next active registry in real-time.
                </p>
            </div>

            <!-- Card 2 -->
            <div class="p-6 rounded-xl bg-white dark:bg-[#111622] border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-950/40 text-[#F6821F] flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Dual Currency & bKash Pay</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Settle invoices instantly with Bangladeshi bKash & Nagad, or internationally via Stripe credit cards, Binance Pay USDT, and automated account wallet credit.
                </p>
            </div>

            <!-- Card 3 -->
            <div class="p-6 rounded-xl bg-white dark:bg-[#111622] border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-950/40 text-[#F6821F] flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Internal Push & Free DNS</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Transfer domains between clients in 1 second with 0 fees. Manage DNS records, Cloudflare & Google Workspace presets, and child nameservers with zero hassle.
                </p>
            </div>
        </div>
    </section>

</div>
@endsection