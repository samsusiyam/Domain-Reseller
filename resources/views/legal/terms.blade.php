@extends('layouts.guest')

@section('content')
<div class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Terms of Service</h1>
        <p class="text-xs text-slate-400 mb-8">Last Updated: January 1, 2026</p>

        <div class="prose prose-slate max-w-none text-sm leading-relaxed space-y-6 text-slate-700">
            <h2 class="text-lg font-bold text-slate-900">1. Acceptance of Terms</h2>
            <p>
                By creating an account, registering domain names, or connecting through our REST API or WHMCS module, you agree to be bound by these Terms of Service and all applicable ICANN regulations and registry policies.
            </p>

            <h2 class="text-lg font-bold text-slate-900">2. Domain Registrations and Ownership</h2>
            <p>
                All domain registrations are processed directly with accredited upstream registrars (including Namecheap, Spaceship, Dynadot, AtakDomain, and NameSilo). You represent that all contact details provided during registration are accurate, truthful, and kept up-to-date. Failure to provide accurate WHOIS data may result in domain suspension per ICANN rules.
            </p>

            <h2 class="text-lg font-bold text-slate-900">3. Wholesale Reseller Accounts & Balance</h2>
            <p>
                Reseller accounts operate on a pre-funded wallet credit system. Domain registrations, renewals, and transfers initiated via API or the reseller dashboard will deduct from your account balance in USD. Pre-funded balances are non-refundable except upon verified account closure where no active domains are maintained.
            </p>

            <h2 class="text-lg font-bold text-slate-900">4. Payments and Multi-Currency Conversion</h2>
            <p>
                All ledger accounting is maintained in USD. For customers paying via bKash or local Bangladesh methods, live conversion to BDT is applied using transparent exchange rates. Chargebacks or fraudulent transactions will lead to immediate domain lock and account termination.
            </p>

            <h2 class="text-lg font-bold text-slate-900">5. Renewals, Grace Periods & Redemption</h2>
            <p>
                Domains must be renewed prior to their expiration date. An auto-renewal feature is available if sufficient wallet credit is maintained. Expired domains enter a registrar grace period (typically 30 days) followed by a redemption period which incurs an additional registry redemption penalty fee.
            </p>
        </div>
    </div>
</div>
@endsection
