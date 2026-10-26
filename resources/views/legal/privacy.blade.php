@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <article class="glass-card p-8 sm:p-12 rounded-3xl border border-white/10 shadow-2xl">
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
            Privacy Policy
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mt-4 mb-2">Privacy & Data Protection</h1>
        <p class="text-xs text-slate-400 mb-8 font-mono">Last Updated: October 2026 • GDPR & ICANN WHOIS Compliance</p>

        <div class="prose prose-invert prose-indigo max-w-none text-sm text-slate-300 leading-relaxed space-y-6 border-t border-white/10 pt-6">
            <h2 class="text-lg font-bold text-white">1. Information We Collect</h2>
            <p>
                To provision domain names and provide billing services, we collect account details (name, email, organization, address, phone number), payment transaction metadata, and domain registrant contact details required by ICANN.
            </p>

            <h2 class="text-lg font-bold text-white">2. WHOIS Privacy & Data Masking</h2>
            <p>
                We provide free WHOIS Privacy Protection on all eligible generic and top-level domain extensions. When activated, your personal email, phone number, and physical street address are masked from public WHOIS lookups.
            </p>

            <h2 class="text-lg font-bold text-white">3. Third-Party Upstream Registry Disclosure</h2>
            <p>
                Domain registration requires transmitting registrant details to accredited registries and upstream registrars (Namecheap, Spaceship, Dynadot, AtakDomain, NameSilo) to complete registry delegations.
            </p>

            <h2 class="text-lg font-bold text-white">4. API Security & Webhooks</h2>
            <p>
                Reseller API requests are validated with SHA-256 HMAC cryptographic signatures and IP whitelists. We log API request endpoints, IP addresses, and response status codes for debugging and audit security.
            </p>
        </div>
    </article>
</div>
@endsection
