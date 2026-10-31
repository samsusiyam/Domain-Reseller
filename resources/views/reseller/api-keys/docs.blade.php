@extends('layouts.reseller')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'whmcs' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Developer API & WHMCS Module</h1>
            <p class="text-sm text-slate-500 mt-1">Complete reference for automated domain provisioning and WHMCS registrar module setup.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="activeTab = 'whmcs'" :class="activeTab === 'whmcs' ? 'bg-[#F6821F] text-white' : 'bg-white dark:bg-[#111827] text-slate-700 hover:bg-slate-50 dark:bg-slate-800/50'" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-800 shadow-sm transition">
                WHMCS Module Guide
            </button>
            <button @click="activeTab = 'api'" :class="activeTab === 'api' ? 'bg-[#F6821F] text-white' : 'bg-white dark:bg-[#111827] text-slate-700 hover:bg-slate-50 dark:bg-slate-800/50'" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-800 shadow-sm transition">
                REST API Reference (44 Endpoints)
            </button>
        </div>
    </div>

    <!-- WHMCS Setup Tab -->
    <div x-show="activeTab === 'whmcs'" class="space-y-6">
        <!-- Step 1: Download & Configuration -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-[#F6821F] text-white font-bold flex items-center justify-center">
                    W
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Official WHMCS Registrar Module</h3>
                    <p class="text-xs text-slate-500">Supports WHMCS v7.10, v8.0+, v8.8, and v9.x</p>
                </div>
            </div>

            <div class="space-y-6 text-sm text-slate-700">
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-950/40 text-[#F6821F] font-bold flex items-center justify-center shrink-0 text-xs">1</div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white mb-1">Download & Extract Module</h4>
                        <p class="text-xs text-slate-600">Extract the custom module into your WHMCS root directory:</p>
                        <div class="mt-2 bg-slate-900 text-slate-200 p-3 rounded-xl font-mono text-xs">
                            /your_whmcs_root/modules/registrars/domainreseller/
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-950/40 text-[#F6821F] font-bold flex items-center justify-center shrink-0 text-xs">2</div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white mb-1">Activate Registrar in WHMCS Admin</h4>
                        <p class="text-xs text-slate-600">Log in to your WHMCS Admin Area &rarr; <strong>Configuration</strong> &rarr; <strong>System Settings</strong> &rarr; <strong>Domain Registrars</strong>. Locate <strong>DomainReseller</strong> and click <strong>Activate</strong>.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-950/40 text-[#F6821F] font-bold flex items-center justify-center shrink-0 text-xs">3</div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white mb-1">Enter Your API Credentials</h4>
                        <p class="text-xs text-slate-600">Fill in the connection details below:</p>
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                                <span class="font-bold text-slate-700 block">API Base URL:</span>
                                <span class="font-mono text-[#F6821F] font-bold">{{ url('/api/v1') }}</span>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                                <span class="font-bold text-slate-700 block">Public Key & Secret:</span>
                                <span class="text-slate-600">Generate from <a href="{{ route('reseller.api-keys.index') }}" class="text-[#F6821F] underline font-semibold">API Credentials</a></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-950/40 text-[#F6821F] font-bold flex items-center justify-center shrink-0 text-xs">4</div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white mb-1">Whitelist Your WHMCS Server IP</h4>
                        <p class="text-xs text-slate-600">Make sure your WHMCS server's outbound dedicated IP address is added to your API Key's IP Whitelist.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- REST API Reference Tab -->
    <div x-show="activeTab === 'api'" x-cloak class="space-y-6">
        <!-- Auth Specifications Card -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-3">Authentication & Base URL</h3>
            <p class="text-xs text-slate-600 mb-4">Every API call must include your API credentials in the HTTP request headers.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-mono text-xs">
                <div class="bg-slate-900 text-slate-200 p-4 rounded-xl">
                    <span class="text-slate-400 font-sans font-bold text-[10px] uppercase block mb-1">Base Endpoint URL:</span>
                    <span class="select-all">{{ url('/api/v1') }}</span>
                </div>
                <div class="bg-slate-900 text-slate-200 p-4 rounded-xl">
                    <span class="text-slate-400 font-sans font-bold text-[10px] uppercase block mb-1">Required Request Headers:</span>
                    <span class="text-emerald-400">X-Api-Key:</span> pk_live_...<br>
                    <span class="text-emerald-400">X-Api-Secret:</span> sk_live_...<br>
                    <span class="text-slate-400">Accept: application/json</span>
                </div>
            </div>
        </div>

        <!-- Endpoints Directory -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">44 REST API Endpoints</h3>
                <p class="text-xs text-slate-500 mt-0.5">Comprehensive programmatic controls for domains, DNS, and account management.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase font-sans font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3">Category</th>
                            <th class="px-6 py-3">Method</th>
                            <th class="px-6 py-3">Endpoint URI</th>
                            <th class="px-6 py-3 font-sans">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <!-- TLD & Availability -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="4">TLDs & Check</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/tld-pricing</td><td class="px-6 py-3 font-sans">Wholesale TLD rates matrix</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/tld-pricing/{tld}</td><td class="px-6 py-3 font-sans">Single TLD pricing & metadata</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/check</td><td class="px-6 py-3 font-sans">Real-time domain availability check</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/check-bulk</td><td class="px-6 py-3 font-sans">Multi-TLD bulk domain lookup</td></tr>

                        <!-- Domain Lifecycle -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="9">Domains</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/domains</td><td class="px-6 py-3 font-sans">List all domains in reseller account</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/domains/{id}</td><td class="px-6 py-3 font-sans">Domain details, dates & registrar status</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/register</td><td class="px-6 py-3 font-sans">Instant domain registration</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/renew</td><td class="px-6 py-3 font-sans">Domain renewal</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/transfer</td><td class="px-6 py-3 font-sans">Initiate domain transfer with EPP code</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/{id}/epp</td><td class="px-6 py-3 font-sans">Retrieve domain EPP transfer auth code</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/{id}/lock</td><td class="px-6 py-3 font-sans">Toggle registrar transfer lock</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/{id}/privacy</td><td class="px-6 py-3 font-sans">Toggle WHOIS ID protection</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/{id}/autorenew</td><td class="px-6 py-3 font-sans">Toggle automated renewal setting</td></tr>

                        <!-- Authoritative Nameservers -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="2">Nameservers</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/domains/{id}/nameservers</td><td class="px-6 py-3 font-sans">Get current nameservers (NS1-NS5)</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-amber-600">PUT</td><td class="px-6 py-3">/domains/{id}/nameservers</td><td class="px-6 py-3 font-sans">Update domain nameservers</td></tr>

                        <!-- Glue Records -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="4">Child NS (Glue)</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/domains/{id}/child-nameservers</td><td class="px-6 py-3 font-sans">List registered host glue records</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/{id}/child-nameservers</td><td class="px-6 py-3 font-sans">Register new child nameserver host</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-amber-600">PUT</td><td class="px-6 py-3">/domains/{id}/child-nameservers/{id}</td><td class="px-6 py-3 font-sans">Update host IP address</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-rose-600">DELETE</td><td class="px-6 py-3">/domains/{id}/child-nameservers/{id}</td><td class="px-6 py-3 font-sans">Delete child nameserver glue record</td></tr>

                        <!-- DNS Management -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="4">DNS Zone</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/domains/{id}/dns</td><td class="px-6 py-3 font-sans">List all DNS zone records</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/{id}/dns</td><td class="px-6 py-3 font-sans">Create DNS record (A, CNAME, MX, TXT)</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-amber-600">PUT</td><td class="px-6 py-3">/domains/{id}/dns/{id}</td><td class="px-6 py-3 font-sans">Update DNS record value or TTL</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-rose-600">DELETE</td><td class="px-6 py-3">/domains/{id}/dns/{id}</td><td class="px-6 py-3 font-sans">Delete DNS zone record</td></tr>

                        <!-- WHOIS Contacts -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="4">WHOIS Contacts</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/domains/{id}/whois</td><td class="px-6 py-3 font-sans">Get Registrant, Admin, Tech & Billing</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-amber-600">PUT</td><td class="px-6 py-3">/domains/{id}/whois</td><td class="px-6 py-3 font-sans">Update domain contact details</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/contacts</td><td class="px-6 py-3 font-sans">List reseller's reusable contacts</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/contacts</td><td class="px-6 py-3 font-sans">Create reusable contact card</td></tr>

                        <!-- Forwarding -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="6">Forwarding</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/domains/{id}/email-forwards</td><td class="px-6 py-3 font-sans">List email aliases</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/{id}/email-forwards</td><td class="px-6 py-3 font-sans">Add email forward alias</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-rose-600">DELETE</td><td class="px-6 py-3">/domains/{id}/email-forwards/{id}</td><td class="px-6 py-3 font-sans">Delete email forward</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/domains/{id}/url-forwards</td><td class="px-6 py-3 font-sans">List URL redirects</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/domains/{id}/url-forwards</td><td class="px-6 py-3 font-sans">Add 301/302/Masked URL redirect</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-rose-600">DELETE</td><td class="px-6 py-3">/domains/{id}/url-forwards/{id}</td><td class="px-6 py-3 font-sans">Delete URL redirect</td></tr>

                        <!-- Account & Billing -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="3">Account & Wallet</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/account/balance</td><td class="px-6 py-3 font-sans">Current balance in USD & BDT</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/account/transactions</td><td class="px-6 py-3 font-sans">Transaction audit ledger</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/system/ping</td><td class="px-6 py-3 font-sans">API health check ping</td></tr>

                        <!-- Webhooks -->
                        <tr><td class="px-6 py-3 font-sans font-bold text-[#F6821F]" rowspan="4">Webhooks</td><td class="px-6 py-3 font-bold text-emerald-600">GET</td><td class="px-6 py-3">/webhooks</td><td class="px-6 py-3 font-sans">List registered webhook endpoints</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/webhooks</td><td class="px-6 py-3 font-sans">Register new webhook endpoint</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-rose-600">DELETE</td><td class="px-6 py-3">/webhooks/{id}</td><td class="px-6 py-3 font-sans">Delete webhook subscription</td></tr>
                        <tr><td class="px-6 py-3 font-bold text-blue-600">POST</td><td class="px-6 py-3">/webhooks/{id}/test</td><td class="px-6 py-3 font-sans">Trigger test webhook ping payload</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
