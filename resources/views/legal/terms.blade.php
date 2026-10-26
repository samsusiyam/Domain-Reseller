@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <article class="glass-card p-8 sm:p-12 rounded-3xl border border-white/10 shadow-2xl">
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
            Legal Agreement
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mt-4 mb-2">Terms of Service</h1>
        <p class="text-xs text-slate-400 mb-8 font-mono">Last Updated: October 2026 • ICANN & Registry Policy Compliance</p>

        <div class="prose prose-invert prose-indigo max-w-none text-sm text-slate-300 leading-relaxed space-y-6 border-t border-white/10 pt-6">
            <h2 class="text-lg font-bold text-white">1. Acceptance of Terms</h2>
            <p>
                By creating an account, registering domain names, or connecting through our REST API or WHMCS module, you agree to be bound by these Terms of Service and all applicable ICANN regulations and upstream registry policies.
            </p>

            <h2 class="text-lg font-bold text-white">2. Domain Registrations and Ownership</h2>
            <p>
                All domain registrations are processed in real time with accredited upstream registrars (Namecheap, Spaceship, Dynadot, AtakDomain, NameSilo). You represent that all contact details provided during registration are accurate, truthful, and kept up-to-date.
            </p>

            <h2 class="text-lg font-bold text-white">3. Wholesale Reseller Accounts & Balance</h2>
            <p>
                Reseller accounts operate on a pre-funded wallet credit system in USD. Domain registrations, renewals, and transfers initiated via API or the reseller dashboard will deduct from your account balance in USD.
            </p>

            <h2 class="text-lg font-bold text-white">4. Multi-Currency Payments & Conversion</h2>
            <p>
                All accounting ledger values are maintained in USD. For customers paying via bKash, local cards, or Binance Pay, live or transparent conversion to BDT is applied.
            </p>

            <h2 class="text-lg font-bold text-white">5. Renewals, Grace Periods & Redemption</h2>
            <p>
                Domains must be renewed prior to their expiration date. An auto-renewal feature is available if sufficient wallet credit is maintained. Expired domains enter a registrar grace period followed by a redemption period which incurs an additional registry redemption penalty fee.
            </p>
        </div>
    </article>
</div>
@endsection
