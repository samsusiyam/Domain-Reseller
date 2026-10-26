@extends('layouts.guest')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-b from-slate-900 to-indigo-950 text-white py-20 px-4 sm:px-6 lg:px-8 text-center">
    <div class="max-w-4xl mx-auto">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 border border-indigo-400/20 text-indigo-300 uppercase tracking-widest mb-4">
            Reseller Partnership
        </span>
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-6">
            Empower Your Hosting Business with Direct Wholesale Domains
        </h1>
        <p class="text-lg text-slate-300 max-w-2xl mx-auto mb-8 leading-relaxed">
            Sell domains to your clients using our turnkey WHMCS Registrar Module or REST API. Multi-registrar routing, zero infrastructure overhead, and automatic multi-currency billing in USD & BDT.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition text-sm shadow-lg shadow-indigo-600/30">
                Join Reseller Program
            </a>
            <a href="{{ route('pricing') }}" class="w-full sm:w-auto px-8 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl transition text-sm border border-slate-700">
                Explore Wholesale Rates
            </a>
        </div>
    </div>
</section>

<!-- Pricing Tiers -->
<section class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-2">Wholesale Tiers</h2>
            <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight">Automatic Volume-Based Margins</h3>
            <p class="text-sm text-slate-600 mt-2">The more domains you register, the higher your profit margin becomes.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Tier 1: Standard -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <h4 class="text-lg font-bold text-slate-900">Standard Tier</h4>
                    <p class="text-xs text-slate-500 mt-1">Starting point for new resellers</p>
                    <div class="my-6">
                        <span class="text-3xl font-black text-slate-900">$0</span>
                        <span class="text-xs text-slate-400">/ initial deposit</span>
                    </div>
                    <ul class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Standard Wholesale Markup</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> WHMCS Module Included</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Full REST API Access</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Free DNS & Email Forwarding</li>
                    </ul>
                </div>
                <a href="{{ route('register') }}" class="mt-8 block text-center py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition">
                    Start with Standard
                </a>
            </div>

            <!-- Tier 2: Silver -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <h4 class="text-lg font-bold text-slate-900">Silver Tier</h4>
                    <p class="text-xs text-slate-500 mt-1">For growing web hosting providers</p>
                    <div class="my-6">
                        <span class="text-3xl font-black text-slate-900">$250</span>
                        <span class="text-xs text-slate-400">/ minimum volume</span>
                    </div>
                    <ul class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 3% Discount Across All TLDs</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Custom Branded Nameservers</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Higher API Rate Limits (120 RPM)</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Priority Ticket Routing</li>
                    </ul>
                </div>
                <a href="{{ route('register') }}" class="mt-8 block text-center py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition">
                    Apply for Silver
                </a>
            </div>

            <!-- Tier 3: Gold (Featured) -->
            <div class="p-6 rounded-2xl bg-indigo-900 text-white shadow-xl shadow-indigo-950/20 flex flex-col justify-between relative border border-indigo-700">
                <span class="absolute -top-3 right-6 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400 text-slate-900">
                    Most Popular
                </span>
                <div>
                    <h4 class="text-lg font-bold">Gold Tier</h4>
                    <p class="text-xs text-indigo-200 mt-1">Established registries & agencies</p>
                    <div class="my-6">
                        <span class="text-3xl font-black text-white">$1,000</span>
                        <span class="text-xs text-indigo-300">/ minimum volume</span>
                    </div>
                    <ul class="space-y-3 text-xs text-indigo-100">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 7% Wholesale Discount</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> White-Label WHOIS Protection</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Real-time Webhooks Delivery</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Dedicated Account Manager</li>
                    </ul>
                </div>
                <a href="{{ route('register') }}" class="mt-8 block text-center py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs rounded-xl transition">
                    Unlock Gold Pricing
                </a>
            </div>

            <!-- Tier 4: Platinum -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <h4 class="text-lg font-bold text-slate-900">Platinum Tier</h4>
                    <p class="text-xs text-slate-500 mt-1">High volume enterprise operations</p>
                    <div class="my-6">
                        <span class="text-3xl font-black text-slate-900">$5,000+</span>
                        <span class="text-xs text-slate-400">/ minimum volume</span>
                    </div>
                    <ul class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Pure Cost + 1.5% Direct Rate</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Unlimited API Rate Limits</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Custom Upstream Registrar Binding</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> SLA Guaranteed 24/7 Phone Line</li>
                    </ul>
                </div>
                <a href="{{ route('contact') }}" class="mt-8 block text-center py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition">
                    Contact for Platinum
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
