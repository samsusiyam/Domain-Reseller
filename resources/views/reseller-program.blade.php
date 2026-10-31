@extends('layouts.guest', ['title' => 'Wholesale Domain Reseller Program'])

@section('content')
<div class="space-y-16 py-10 sm:py-14">

    <!-- 1. Hero Section -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-left sm:text-center">
        <div class="mb-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] text-xs font-mono font-medium border"
                  style="background-color: var(--surface); border-color: var(--border); color: var(--text);">
                <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                <span>Turnkey Wholesale Registry</span>
            </span>
        </div>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight leading-tight max-w-3xl mx-auto" style="color: var(--text);">
            Resell domains under your brand with direct upstream registry access.
        </h1>

        <p class="text-sm sm:text-base mt-3 max-w-2xl mx-auto leading-relaxed" style="color: var(--text-muted);">
            Automate provisioning for your web hosting clients using our native WHMCS registrar module or 44-endpoint REST API. Multi-registrar routing across 6 registries with dual USD and BDT settlement.
        </p>

        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('register') }}?role=reseller"
               class="cf-btn cf-btn-primary h-10 px-6 font-medium text-sm w-full sm:w-auto">
                Apply for reseller account
            </a>
            <a href="#whmcs"
               class="cf-btn cf-btn-secondary h-10 px-6 font-medium text-sm w-full sm:w-auto">
                View WHMCS module
            </a>
        </div>
    </section>

    <!-- 2. Volume Pricing Tiers -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-xl font-semibold tracking-tight" style="color: var(--text);">Volume pricing tiers</h2>
            <p class="text-xs mt-1" style="color: var(--text-muted);">Account tiers automatically scale based on wallet deposit and monthly volume.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tier 1 -->
            <div class="cf-panel p-5 flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-mono uppercase tracking-wider block mb-1" style="color: var(--text-muted);">Tier 1</span>
                    <h3 class="text-base font-semibold" style="color: var(--text);">Standard</h3>
                    <div class="my-4">
                        <span class="text-2xl font-bold font-mono" style="color: var(--text);">$0</span>
                        <span class="text-xs" style="color: var(--text-muted);">/ minimum deposit</span>
                    </div>
                    <ul class="space-y-2 text-xs" style="color: var(--text-muted);">
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> Catalog Wholesale Rates</li>
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> Turnkey WHMCS Module</li>
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> 44 REST API Endpoints</li>
                    </ul>
                </div>
                <div class="mt-6">
                    <a href="{{ route('register') }}?role=reseller" class="cf-btn cf-btn-secondary w-full text-xs">Start standard</a>
                </div>
            </div>

            <!-- Tier 2 -->
            <div class="cf-panel p-5 flex flex-col justify-between" style="border-color: var(--accent);">
                <div>
                    <span class="text-[11px] font-mono uppercase tracking-wider block mb-1" style="color: var(--accent);">Tier 2 • Popular</span>
                    <h3 class="text-base font-semibold" style="color: var(--text);">Silver</h3>
                    <div class="my-4">
                        <span class="text-2xl font-bold font-mono" style="color: var(--text);">$250</span>
                        <span class="text-xs" style="color: var(--text-muted);">/ wallet credit</span>
                    </div>
                    <ul class="space-y-2 text-xs" style="color: var(--text-muted);">
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> <strong>5% Extra Discount</strong> on TLDs</li>
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> Priority API Rate Limits</li>
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> Instant DNS Failover</li>
                    </ul>
                </div>
                <div class="mt-6">
                    <a href="{{ route('register') }}?role=reseller" class="cf-btn cf-btn-primary w-full text-xs">Get Silver</a>
                </div>
            </div>

            <!-- Tier 3 -->
            <div class="cf-panel p-5 flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-mono uppercase tracking-wider block mb-1" style="color: var(--text-muted);">Tier 3</span>
                    <h3 class="text-base font-semibold" style="color: var(--text);">Gold</h3>
                    <div class="my-4">
                        <span class="text-2xl font-bold font-mono" style="color: var(--text);">$1,000</span>
                        <span class="text-xs" style="color: var(--text-muted);">/ wallet credit</span>
                    </div>
                    <ul class="space-y-2 text-xs" style="color: var(--text-muted);">
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> <strong>10% Extra Discount</strong> on TLDs</li>
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> Dedicated Account Manager</li>
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> Custom EPP Integration</li>
                    </ul>
                </div>
                <div class="mt-6">
                    <a href="{{ route('register') }}?role=reseller" class="cf-btn cf-btn-secondary w-full text-xs">Get Gold</a>
                </div>
            </div>

            <!-- Tier 4 -->
            <div class="cf-panel p-5 flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-mono uppercase tracking-wider block mb-1" style="color: var(--text-muted);">Tier 4</span>
                    <h3 class="text-base font-semibold" style="color: var(--text);">Platinum</h3>
                    <div class="my-4">
                        <span class="text-2xl font-bold font-mono" style="color: var(--text);">$2,500</span>
                        <span class="text-xs" style="color: var(--text-muted);">/ wallet credit</span>
                    </div>
                    <ul class="space-y-2 text-xs" style="color: var(--text-muted);">
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> <strong>Registry At-Cost</strong> Pricing</li>
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> Custom Credit Terms</li>
                        <li class="flex items-center gap-2"><span class="text-[#16A34A] font-bold">✓</span> Direct ICANN Compliance Liaison</li>
                    </ul>
                </div>
                <div class="mt-6">
                    <a href="{{ route('contact') }}" class="cf-btn cf-btn-secondary w-full text-xs">Contact sales</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. WHMCS & API Section -->
    <section id="whmcs" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="cf-panel p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 rounded bg-[#F6821F] text-white flex items-center justify-center font-bold text-xs">W</span>
                    <h3 class="font-semibold text-base" style="color: var(--text);">Turnkey WHMCS Module</h3>
                </div>
                <p class="text-xs leading-relaxed mb-4" style="color: var(--text-muted);">
                    Drop our registrar module directly into <code>modules/registrars/domainreseller/</code>. Features instant domain registration, transfers, auto-renewals, DNS management, and nameserver synchronization.
                </p>
                <div class="p-3 rounded-[6px] border text-xs font-mono space-y-1" style="background-color: var(--surface); border-color: var(--border); color: var(--text);">
                    <div>$configarray = [</div>
                    <div class="pl-4">'FriendlyName' => ['Type' => 'System', 'Value' => 'DomainReseller'],</div>
                    <div class="pl-4">'ApiKey' => ['Type' => 'password', 'Size' => '40'],</div>
                    <div>];</div>
                </div>
            </div>

            <div class="cf-panel p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 rounded bg-[#111827] text-white flex items-center justify-center font-bold text-xs">API</span>
                    <h3 class="font-semibold text-base" style="color: var(--text);">44 REST Endpoints</h3>
                </div>
                <p class="text-xs leading-relaxed mb-4" style="color: var(--text-muted);">
                    Developer-first API with token authorization, IP whitelisting, webhook delivery, and sandbox testing endpoints.
                </p>
                <div class="p-3 rounded-[6px] border text-xs font-mono space-y-1" style="background-color: var(--surface); border-color: var(--border); color: var(--text);">
                    <div>POST /api/v1/domains/register</div>
                    <div>Host: api.hostnibo.com</div>
                    <div>Authorization: Bearer dr_live_sec_...</div>
                    <div class="text-[#16A34A]">{ "domain": "brand.com", "years": 1 }</div>
                </div>
            </div>

        </div>
    </section>

</div>
@endsection
