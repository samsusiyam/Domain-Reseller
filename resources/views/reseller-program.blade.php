@extends('layouts.guest')

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden py-24 px-4 sm:px-6 lg:px-8 border-b border-white/5">
    <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[350px] bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto text-center relative z-10">
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 uppercase tracking-widest mb-6">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            Turnkey Reseller Infrastructure
        </span>
        <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-tight mb-6">
            Resell Domains Under Your Brand with <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-sky-300 to-violet-400">Zero Overhead</span>
        </h1>
        <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed">
            Sell domains to your clients using our turnkey WHMCS Registrar Module or 44-endpoint REST API. Multi-registrar routing across 5 registries with automated dual-currency billing in USD & BDT.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}"
                class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold rounded-xl transition shadow-xl shadow-indigo-600/30 text-sm">
                Join Reseller Program Free →
            </a>
            <a href="#whmcs"
                class="w-full sm:w-auto px-8 py-4 bg-white/5 hover:bg-white/10 text-white font-bold rounded-xl transition text-sm border border-white/10">
                View WHMCS Integration
            </a>
        </div>
    </div>
</section>

<!-- Pricing Tiers Section -->
<section class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-2 block">Volume Margins</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Automatic Wholesale Pricing Tiers</h2>
        <p class="text-sm text-slate-400 mt-2">The more domains you provision, the lower your wholesale registry costs become.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Tier 1: Standard -->
        <div class="p-6 rounded-3xl glass-card flex flex-col justify-between hover:border-white/20 transition">
            <div>
                <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block mb-2">Level 1</span>
                <h3 class="text-xl font-extrabold text-white">Standard Tier</h3>
                <p class="text-xs text-slate-400 mt-1">Starting point for new resellers</p>
                <div class="my-6">
                    <span class="text-3xl font-black text-white">$0</span>
                    <span class="text-xs text-slate-500 font-mono">/ deposit</span>
                </div>
                <ul class="space-y-3 text-xs text-slate-300">
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Base Wholesale Rates</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Full WHMCS Module</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> 44 REST API Endpoints</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Free WHOIS Privacy</li>
                </ul>
            </div>
            <a href="{{ route('register') }}" class="mt-8 w-full py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white font-bold text-xs text-center transition">
                Start Standard
            </a>
        </div>

        <!-- Tier 2: Silver -->
        <div class="p-6 rounded-3xl glass-card flex flex-col justify-between hover:border-indigo-500/40 transition">
            <div>
                <span class="text-[10px] font-mono uppercase tracking-wider text-indigo-400 font-bold block mb-2">Level 2</span>
                <h3 class="text-xl font-extrabold text-white">Silver Tier</h3>
                <p class="text-xs text-slate-400 mt-1">Active hosting providers</p>
                <div class="my-6">
                    <span class="text-3xl font-black text-indigo-400">$250</span>
                    <span class="text-xs text-slate-500 font-mono">/ wallet credit</span>
                </div>
                <ul class="space-y-3 text-xs text-slate-300">
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> <strong>5% Extra Discount</strong> on TLDs</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Priority API Rate Limits</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Instant Order Execution</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Custom Child NS Support</li>
                </ul>
            </div>
            <a href="{{ route('register') }}" class="mt-8 w-full py-2.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600 border border-indigo-500/30 text-indigo-300 hover:text-white font-bold text-xs text-center transition">
                Deposit $250
            </a>
        </div>

        <!-- Tier 3: Gold (Popular) -->
        <div class="p-6 rounded-3xl glass-card border-2 border-indigo-500/60 shadow-xl shadow-indigo-600/20 flex flex-col justify-between relative">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-indigo-600 text-[10px] font-bold text-white uppercase tracking-wider">
                Most Popular
            </span>
            <div>
                <span class="text-[10px] font-mono uppercase tracking-wider text-amber-400 font-bold block mb-2">Level 3</span>
                <h3 class="text-xl font-extrabold text-white">Gold Tier</h3>
                <p class="text-xs text-slate-400 mt-1">High volume registries</p>
                <div class="my-6">
                    <span class="text-3xl font-black text-amber-400">$500</span>
                    <span class="text-xs text-slate-500 font-mono">/ wallet credit</span>
                </div>
                <ul class="space-y-3 text-xs text-slate-300">
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> <strong>10% Extra Discount</strong> on TLDs</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Custom White-Label DNS</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Webhook Event Dispatcher</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Dedicated Account Manager</li>
                </ul>
            </div>
            <a href="{{ route('register') }}" class="mt-8 w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs text-center transition shadow-md">
                Deposit $500
            </a>
        </div>

        <!-- Tier 4: Platinum -->
        <div class="p-6 rounded-3xl glass-card flex flex-col justify-between hover:border-purple-500/40 transition">
            <div>
                <span class="text-[10px] font-mono uppercase tracking-wider text-purple-400 font-bold block mb-2">Level 4</span>
                <h3 class="text-xl font-extrabold text-white">Platinum Tier</h3>
                <p class="text-xs text-slate-400 mt-1">Enterprise domain resellers</p>
                <div class="my-6">
                    <span class="text-3xl font-black text-purple-400">$1,000+</span>
                    <span class="text-xs text-slate-500 font-mono">/ wallet credit</span>
                </div>
                <ul class="space-y-3 text-xs text-slate-300">
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> <strong>15% Max Wholesale Margin</strong></li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Direct Registry Redundancy</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Custom Upstream Routing</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> 24/7 Priority Emergency SLA</li>
                </ul>
            </div>
            <a href="{{ route('register') }}" class="mt-8 w-full py-2.5 rounded-xl bg-purple-600/20 hover:bg-purple-600 border border-purple-500/30 text-purple-300 hover:text-white font-bold text-xs text-center transition">
                Deposit $1,000
            </a>
        </div>
    </div>
</section>

<!-- WHMCS Module Section -->
<section id="whmcs" class="py-20 px-4 sm:px-6 lg:px-8 border-t border-white/10 bg-obsidian-900/60">
    <div class="max-w-6xl mx-auto">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-500/30">
                1-Minute Setup
            </span>
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight mt-4">
                Official WHMCS Registrar Module
            </h2>
            <p class="text-slate-400 mt-3 text-base">
                Download and drop into your WHMCS modules folder. Instant automated provisioning with zero code changes.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 rounded-2xl glass-card border border-white/10">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-mono font-bold flex items-center justify-center mb-4">1</div>
                <h3 class="text-lg font-bold text-white mb-2">Download Module</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Grab the official module from your reseller portal and upload the files to:
                    <code class="block mt-2 p-2 rounded bg-black/40 text-indigo-300 text-[11px] font-mono">/modules/registrars/domainreseller/</code>
                </p>
            </div>

            <div class="p-6 rounded-2xl glass-card border border-white/10">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-mono font-bold flex items-center justify-center mb-4">2</div>
                <h3 class="text-lg font-bold text-white mb-2">Configure API Keys</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    In WHMCS Admin ➔ System Settings ➔ Domain Registrars, activate <strong>DomainReseller</strong> and enter your API Key and Secret.
                </p>
            </div>

            <div class="p-6 rounded-2xl glass-card border border-white/10">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-mono font-bold flex items-center justify-center mb-4">3</div>
                <h3 class="text-lg font-bold text-white mb-2">Automate Provisioning</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Link your TLDs (.com, .net, .org, .xyz) to the registrar module. WHMCS handles client billing while we execute registry orders.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- REST API Section -->
<section id="api" class="py-20 px-4 sm:px-6 lg:px-8 border-t border-white/10 max-w-6xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest block mb-2">Developer Friendly</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">External REST API Overview</h2>
        <p class="text-sm text-slate-400 mt-2">44 fully-documented endpoints for custom provisioning, Blesta, or internal billing systems.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-mono">
        <div class="p-4 rounded-xl glass-card border border-white/10">
            <span class="text-emerald-400 font-bold">GET</span>
            <span class="text-white ml-1">/api/v1/domains/check</span>
            <p class="text-slate-400 text-[11px] font-sans mt-1">Single domain availability & pricing</p>
        </div>
        <div class="p-4 rounded-xl glass-card border border-white/10">
            <span class="text-indigo-400 font-bold">POST</span>
            <span class="text-white ml-1">/api/v1/domains/register</span>
            <p class="text-slate-400 text-[11px] font-sans mt-1">Instant registration execution</p>
        </div>
        <div class="p-4 rounded-xl glass-card border border-white/10">
            <span class="text-indigo-400 font-bold">POST</span>
            <span class="text-white ml-1">/api/v1/domains/renew</span>
            <p class="text-slate-400 text-[11px] font-sans mt-1">Automated renewal execution</p>
        </div>
        <div class="p-4 rounded-xl glass-card border border-white/10">
            <span class="text-purple-400 font-bold">PUT</span>
            <span class="text-white ml-1">/api/v1/domains/{d}/dns</span>
            <p class="text-slate-400 text-[11px] font-sans mt-1">Manage A, CNAME, MX, TXT records</p>
        </div>
    </div>
</section>
@endsection
