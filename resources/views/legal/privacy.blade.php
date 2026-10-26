@extends('layouts.guest')

@section('content')
<div class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Privacy Policy</h1>
        <p class="text-xs text-slate-400 mb-8">Last Updated: January 1, 2026</p>

        <div class="prose prose-slate max-w-none text-sm leading-relaxed space-y-6 text-slate-700">
            <h2 class="text-lg font-bold text-slate-900">1. Information We Collect</h2>
            <p>
                To provide domain registration, billing, and API services, we collect personal information including full name, company name, email address, postal address, phone number, and IP addresses. We also store transaction logs and API request metadata for security audit compliance.
            </p>

            <h2 class="text-lg font-bold text-slate-900">2. WHOIS Information & Privacy Protection</h2>
            <p>
                Domain registries require contact information for domain records. If WHOIS ID Protection is enabled on supported TLDs, your personal details are replaced with a proxy privacy service to prevent spam and unsolicited inquiries.
            </p>

            <h2 class="text-lg font-bold text-slate-900">3. Security and Storage</h2>
            <p>
                All sensitive API keys and secrets are stored using one-way cryptographic SHA-256 hashes or AES-256 encryption. We enforce Two-Factor Authentication (2FA) and optional IP whitelisting on all wholesale API integrations.
            </p>

            <h2 class="text-lg font-bold text-slate-900">4. Third-Party Data Sharing</h2>
            <p>
                Your registration data is shared strictly with accredited upstream domain registries and ICANN-accredited registrars for provisioning purposes. We do not sell or rent customer data to third-party advertisers.
            </p>
        </div>
    </div>
</div>
@endsection
