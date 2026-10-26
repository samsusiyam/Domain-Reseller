@extends('layouts.app', ['title' => 'Nameservers - ' . $domain->domain_name, 'header' => 'Authoritative Nameservers'])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Domain Sub-navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto border-b border-slate-200 pb-2 text-xs font-bold uppercase tracking-wider text-slate-600">
        <a href="{{ route('client.domains.show', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Overview</a>
        <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg bg-indigo-50 text-indigo-600 whitespace-nowrap">Nameservers</a>
        <a href="{{ route('client.domains.dns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">DNS Zone</a>
        <a href="{{ route('client.domains.child-ns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Child Nameservers</a>
        <a href="{{ route('client.domains.whois.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">WHOIS Contacts</a>
        <a href="{{ route('client.domains.email-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Email Forwards</a>
        <a href="{{ route('client.domains.url-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">URL Redirects</a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900 mb-1">Authoritative Nameservers for {{ $domain->domain_name }}</h2>
        <p class="text-xs text-slate-500 mb-6">
            Nameservers determine where your domain points for DNS lookups (e.g., Cloudflare, cPanel, or our system DNS).
        </p>

        <form action="{{ route('client.domains.nameservers.update', $domain->id) }}" method="POST" class="space-y-4 max-w-xl">
            @csrf
            @method('PUT')

            @php
                $ns = $domain->nameservers ?? [];
            @endphp

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nameserver 1</label>
                <input type="text" name="ns1" value="{{ old('ns1', $ns[0] ?? config('dns_templates.default_nameservers.ns1')) }}" required class="w-full px-4 py-2.5 font-mono text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nameserver 2</label>
                <input type="text" name="ns2" value="{{ old('ns2', $ns[1] ?? config('dns_templates.default_nameservers.ns2')) }}" required class="w-full px-4 py-2.5 font-mono text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nameserver 3 (Optional)</label>
                <input type="text" name="ns3" value="{{ old('ns3', $ns[2] ?? '') }}" class="w-full px-4 py-2.5 font-mono text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nameserver 4 (Optional)</label>
                <input type="text" name="ns4" value="{{ old('ns4', $ns[3] ?? '') }}" class="w-full px-4 py-2.5 font-mono text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="pt-4">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow">
                    Save Nameservers
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
