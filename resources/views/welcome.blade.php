@extends('layouts.guest')

@section('content')
<div class="space-y-16 sm:space-y-24 py-8 sm:py-12">

    <!-- 1. HERO SECTION (Left-aligned, 2-column, 48px H1, 56px search, real preview card) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

            <!-- Left Column: Heading, Subtitle, 56px Search Bar, Plain Text TLD Prices -->
            <div class="lg:col-span-7 space-y-6 text-left">

                <h1 class="text-[36px] sm:text-[44px] lg:text-[48px] font-semibold text-[#111827] tracking-tight leading-[1.1] max-w-xl">
                    Register domains at wholesale prices. Pay in BDT or USD.
                </h1>

                <p class="text-[15px] sm:text-[16px] text-[#6B7280] max-w-lg leading-relaxed">
                    Direct upstream connections to Namecheap, Spaceship, Dynadot, and NameSilo with automated DNS and WHMCS integration.
                </p>

                <!-- Search Bar Container (56px) -->
                <div class="pt-2 max-w-xl">
                    <form action="{{ route('domain.search') }}" method="GET" class="relative">
                        <div class="relative flex items-center h-14 bg-white border border-[#E5E7EB] rounded-[6px] focus-within:border-[#F6821F] focus-within:ring-1 focus-within:ring-[#F6821F] transition">
                            <svg class="w-5 h-5 text-[#9CA3AF] ml-4 absolute pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <input type="text"
                                   name="query"
                                   placeholder="Find your domain name (e.g. mycompany.com)..."
                                   required
                                   class="w-full h-full pl-12 pr-28 text-[14px] sm:text-[15px] text-[#111827] placeholder-[#9CA3AF] bg-transparent border-0 focus:outline-none focus:ring-0">
                            <button type="submit"
                                    class="absolute right-1.5 top-1.5 bottom-1.5 px-5 bg-[#F6821F] hover:bg-[#E07116] text-white text-[14px] font-medium rounded-[4px] transition flex items-center justify-center">
                                Search
                            </button>
                        </div>
                    </form>

                    <!-- Under Search: Support bulk search + popular TLD prices as plain text -->
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-y-2 text-[12px] text-[#6B7280]">
                        <div class="flex flex-wrap items-center gap-x-2.5">
                            @php
                                $heroTlds = $popularTlds->take(5);
                            @endphp
                            @forelse($heroTlds as $tld)
                                <span>
                                    <strong class="font-medium text-[#111827]">{{ $tld->tld }}</strong> from ${{ number_format((float)$tld->client_register, 2) }}/yr
                                </span>
                                @if(!$loop->last)
                                    <span class="text-[#D1D5DB]">·</span>
                                @endif
                            @empty
                                <span><strong class="font-medium text-[#111827]">.com</strong> from $10.49/yr</span>
                                <span class="text-[#D1D5DB]">·</span>
                                <span><strong class="font-medium text-[#111827]">.net</strong> from $11.99/yr</span>
                                <span class="text-[#D1D5DB]">·</span>
                                <span><strong class="font-medium text-[#111827]">.org</strong> from $12.49/yr</span>
                                <span class="text-[#D1D5DB]">·</span>
                                <span><strong class="font-medium text-[#111827]">.xyz</strong> from $2.49/yr</span>
                                <span class="text-[#D1D5DB]">·</span>
                                <span><strong class="font-medium text-[#111827]">.io</strong> from $34.99/yr</span>
                            @endforelse
                        </div>

                        <!-- Bulk search link -->
                        <a href="{{ route('domain.search', ['bulk' => 1]) }}" class="text-[#F6821F] hover:underline font-medium shrink-0">
                            Search multiple &rarr;
                        </a>
                    </div>
                </div>

            </div>

            <!-- Right Column: Real-looking, clean search result preview card -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end">
                <div class="w-full max-w-md bg-white border border-[#E5E7EB] rounded-[8px] p-5 space-y-4 shadow-none">

                    <!-- Preview Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-mono uppercase text-[#6B7280] font-medium">Domain Availability</span>
                        </div>
                        <span class="text-[11px] text-[#6B7280]">Real-time query</span>
                    </div>

                    <!-- Main Highlight Domain Card -->
                    <div class="p-3.5 bg-[#F7F7F8] border border-[#E5E7EB] rounded-[6px] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-semibold text-[16px] text-[#111827]">examplebrand.com</span>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                                <span class="text-[12px] font-medium text-[#16A34A]">Available</span>
                            </div>
                        </div>

                        <div class="flex items-baseline justify-between pt-1">
                            <div>
                                <span class="font-mono font-semibold text-[18px] text-[#111827] tabular-nums">$10.49</span>
                                <span class="text-[12px] text-[#6B7280]">/year</span>
                                <span class="block text-[11px] text-[#6B7280] tabular-nums">≈ ৳1,258 BDT</span>
                            </div>

                            <a href="{{ route('domain.search', ['query' => 'examplebrand.com']) }}"
                               class="cf-btn cf-btn-primary cf-btn-sm">
                                Add to cart
                            </a>
                        </div>
                    </div>

                    <!-- Secondary Extensions List -->
                    <div class="divide-y divide-[#E5E7EB] text-[13px]">
                        <!-- Row 1: .net -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                                <span class="font-mono text-[#111827]">examplebrand.net</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-[13px] text-[#111827] tabular-nums">$11.99</span>
                                <a href="{{ route('domain.search', ['query' => 'examplebrand.net']) }}"
                                   class="cf-btn cf-btn-secondary cf-btn-sm text-[12px]">
                                    Add
                                </a>
                            </div>
                        </div>

                        <!-- Row 2: .org (Taken) -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#9CA3AF]"></span>
                                <span class="font-mono text-[#6B7280]">examplebrand.org</span>
                            </div>
                            <span class="text-[12px] text-[#6B7280]">Taken</span>
                        </div>

                        <!-- Row 3: .xyz -->
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                                <span class="font-mono text-[#111827]">examplebrand.xyz</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-[13px] text-[#111827] tabular-nums">$2.49</span>
                                <a href="{{ route('domain.search', ['query' => 'examplebrand.xyz']) }}"
                                   class="cf-btn cf-btn-secondary cf-btn-sm text-[12px]">
                                    Add
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer Checkmarks -->
                    <div class="pt-2 border-t border-[#E5E7EB] text-[11px] text-[#6B7280] flex items-center justify-between">
                        <span>Includes WHOIS privacy</span>
                        <span>Free DNS hosting</span>
                        <span>EPP code unlock</span>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- 2. LOGOS STRIP: "Connected to" + registrar logos in grayscale (One row, small) -->
    <section class="border-y border-[#E5E7EB] bg-[#F7F7F8] py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-center sm:justify-between gap-6 text-[12px] text-[#6B7280]">
                <span class="font-medium shrink-0 uppercase tracking-wider text-[11px]">Connected upstream to:</span>

                <div class="flex flex-wrap items-center gap-8 font-mono font-semibold text-[13px] text-[#6B7280] tracking-wider grayscale opacity-80">
                    <span class="hover:text-[#111827] transition">NAMECHEAP</span>
                    <span class="text-[#D1D5DB]">/</span>
                    <span class="hover:text-[#111827] transition">SPACESHIP</span>
                    <span class="text-[#D1D5DB]">/</span>
                    <span class="hover:text-[#111827] transition">DYNADOT</span>
                    <span class="text-[#D1D5DB]">/</span>
                    <span class="hover:text-[#111827] transition">ATAKDOMAIN</span>
                    <span class="text-[#D1D5DB]">/</span>
                    <span class="hover:text-[#111827] transition">NAMESILO</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. PRICING TABLE SECTION: "Popular extensions" (8-10 rows, single source of truth) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <h2 class="text-[20px] font-semibold text-[#111827] leading-tight">Popular extensions</h2>
                    <p class="text-[13px] text-[#6B7280] mt-0.5">Wholesale pricing across top domain extensions with transparent renewal rates.</p>
                </div>
                <a href="{{ route('pricing') }}" class="text-[13px] text-[#F6821F] hover:underline font-medium inline-flex items-center gap-1 shrink-0">
                    See all extensions &rarr;
                </a>
            </div>

            <!-- Clean Table Container -->
            <div class="border border-[#E5E7EB] rounded-[8px] bg-white overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[12px] text-[#6B7280] font-normal">
                            <tr>
                                <th class="px-5 py-3 font-medium">TLD</th>
                                <th class="px-5 py-3 font-medium text-right">Register</th>
                                <th class="px-5 py-3 font-medium text-right">Renew</th>
                                <th class="px-5 py-3 font-medium text-right">Transfer</th>
                                <th class="px-5 py-3 font-medium">Included features</th>
                                <th class="px-5 py-3 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @forelse($popularTlds as $tld)
                                <tr class="hover:bg-[#F7F7F8] transition-colors h-[44px]">
                                    <!-- TLD -->
                                    <td class="px-5 py-2.5 font-mono font-semibold text-[#111827] text-[14px]">
                                        {{ $tld->tld }}
                                    </td>

                                    <!-- Register -->
                                    <td class="px-5 py-2.5 text-right font-mono font-medium text-[#111827] tabular-nums">
                                        ${{ number_format((float)$tld->client_register, 2) }}
                                    </td>

                                    <!-- Renew -->
                                    <td class="px-5 py-2.5 text-right font-mono text-[#6B7280] tabular-nums">
                                        ${{ number_format((float)$tld->client_renew, 2) }}
                                    </td>

                                    <!-- Transfer -->
                                    <td class="px-5 py-2.5 text-right font-mono text-[#6B7280] tabular-nums">
                                        ${{ number_format((float)$tld->client_transfer, 2) }}
                                    </td>

                                    <!-- Features -->
                                    <td class="px-5 py-2.5 text-[#6B7280] text-[12px]">
                                        Free WHOIS privacy · DNS management
                                    </td>

                                    <!-- Action -->
                                    <td class="px-5 py-2.5 text-right">
                                        <a href="{{ route('domain.search', ['query' => 'domain' . $tld->tld]) }}"
                                           class="cf-btn cf-btn-secondary cf-btn-sm">
                                            Register
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-[#6B7280]">
                                        No active TLD pricing configured yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-[#F7F7F8] border-t border-[#E5E7EB] text-[12px] text-[#6B7280]">
                            <tr>
                                <td colspan="6" class="px-5 py-3 flex items-center justify-between">
                                    <span>Prices shown in USD. Instant conversion to BDT during checkout.</span>
                                    <a href="{{ route('pricing') }}" class="text-[#F6821F] hover:underline font-medium">
                                        View complete pricing matrix &rarr;
                                    </a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
