@extends('layouts.guest', ['title' => 'Privacy Policy - HostNibo Domain Hub'])

@section('content')
<div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-[4px] text-xs font-medium bg-[#ECFDF5] text-[#059669] border border-[#A7F3D0] mb-3">
            Data Protection
        </div>
        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight" style="color: var(--text);">Privacy & Data Policy</h1>
        <p class="text-xs mt-1 font-mono" style="color: var(--text-muted);">Last Updated: October 2026 • GDPR & ICANN WHOIS Compliance</p>
    </div>

    <div class="cf-panel p-6 sm:p-10 space-y-8 text-sm leading-relaxed" style="color: var(--text);">
        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">1. Information We Collect</h2>
            <p style="color: var(--text-muted);">
                To provision domain names and provide billing services, we collect account details (name, email, organization, address, phone number), payment transaction metadata, and domain registrant contact details required by ICANN. We do not sell or monetize personal customer records to third parties.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">2. Free WHOIS Privacy & Data Masking</h2>
            <p style="color: var(--text-muted);">
                We provide free WHOIS Privacy Protection on all eligible generic and top-level domain extensions. When activated, your personal email, phone number, and physical street address are masked from public RDAP/WHOIS lookups to protect against spam, scrapers, and identity harvesting.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">3. Transmission to Upstream Registry Operators</h2>
            <p style="color: var(--text-muted);">
                Domain registration by international governance requires transmitting registrant details to accredited registries (such as Verisign, Public Interest Registry, Identity Digital) and upstream registrars (Namecheap, Spaceship, Dynadot) solely to complete technical registry delegations.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">4. API Security & Access Logs</h2>
            <p style="color: var(--text-muted);">
                Reseller API requests are validated with SHA-256 HMAC cryptographic signatures and IP whitelists. We log API request endpoints, IP addresses, and response status codes for debugging, brute-force mitigation, and security compliance.
            </p>
        </section>
    </div>
</div>
@endsection
