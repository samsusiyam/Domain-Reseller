@extends('layouts.guest', ['title' => 'Terms of Service - HostNibo Domain Hub'])

@section('content')
<div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-[4px] text-xs font-medium bg-[#FFF7ED] text-[#F6821F] border border-[#FFEDD5] mb-3">
            Legal Agreement
        </div>
        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight" style="color: var(--text);">Terms of Service</h1>
        <p class="text-xs mt-1 font-mono" style="color: var(--text-muted);">Last Updated: October 2026 • ICANN & Upstream Registry Policy Compliance</p>
    </div>

    <div class="cf-panel p-6 sm:p-10 space-y-8 text-sm leading-relaxed" style="color: var(--text);">
        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">1. Acceptance of Terms</h2>
            <p style="color: var(--text-muted);">
                By creating an account, registering or transferring domain names, or connecting through our REST API or WHMCS registrar module, you agree to be bound by these Terms of Service, all applicable ICANN regulations, and upstream registry consensus policies. If you do not agree with these terms, do not access or use the HostNibo Domain Hub platform.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">2. Domain Registrations and Ownership</h2>
            <p style="color: var(--text-muted);">
                All domain registrations are provisioned in real time with accredited upstream registrars (Namecheap, Spaceship, Dynadot, NameSilo) and registry operators. The registrant designated during the checkout or API creation is the legal owner of the domain license for the paid term. You certify that all registrant, administrative, technical, and billing details provided are accurate and truthful as mandated by ICANN WHOIS accuracy guidelines.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">3. Wholesale Reseller Accounts & Balance</h2>
            <p style="color: var(--text-muted);">
                Reseller accounts operate on a pre-funded wallet credit system in USD/BDT. Any automated API request or portal operation (registration, renewal, transfer, WHOIS privacy toggle) will instantly deduct the requisite wholesale cost from your available wallet balance. Resellers are solely responsible for maintaining adequate balance to guarantee successful automated renewals.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">4. Multi-Currency Payments & Transparent Conversion</h2>
            <p style="color: var(--text-muted);">
                Base pricing and accounting ledgers are maintained in USD. Customers choosing to fund their balance or pay invoices via bKash, local mobile banking, or Binance Pay agree to the published conversion rates at the exact time of invoice creation.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">5. Renewals, Grace Periods & Redemption</h2>
            <p style="color: var(--text-muted);">
                Domains must be renewed prior to their registry expiration date. Expired generic domains enter a registrar Auto-Renew Grace Period (typically 30 days) followed by a 30-day Redemption Period. Restoring a domain during the Redemption Period requires paying an upstream registry penalty redemption fee in addition to the standard renewal rate.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">6. Prohibited Activities & Abuse Takedowns</h2>
            <p style="color: var(--text-muted);">
                Domains registered through HostNibo may not be utilized for phishing, malware distribution, child sexual abuse material (CSAM), botnets, or illegal activities. HostNibo reserves the right to lock or suspend domains upon verified law enforcement alerts or ICANN compliance notices.
            </p>
        </section>
    </div>
</div>
@endsection
