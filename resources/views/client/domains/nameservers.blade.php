@extends('layouts.app', ['title' => 'Nameservers - ' . $domain->domain_name, 'header' => 'Authoritative Nameservers'])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Domain Sub-navigation Tabs -->
    <x-domain-tabs :domain="$domain" active="nameservers" />

    <div class="bg-white dark:bg-[#111827] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight mb-1">Authoritative Nameservers for {{ $domain->domain_name }}</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
            Nameservers delegate DNS lookups (e.g., Cloudflare, HostNibo DNS, or cPanel hosting). Propagation typically takes 15–30 minutes.
        </p>

        <form action="{{ route('client.domains.nameservers.update', $domain->id) }}" method="POST" class="space-y-4 max-w-xl">
            @csrf
            @method('PUT')

            @php
                $ns = $domain->nameservers ?? [];
            @endphp

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Primary Nameserver 1</label>
                <input type="text" name="ns1" value="{{ old('ns1', $ns[0] ?? config('dns_templates.default_nameservers.ns1')) }}" required class="cf-input h-9 text-xs font-mono">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Secondary Nameserver 2</label>
                <input type="text" name="ns2" value="{{ old('ns2', $ns[1] ?? config('dns_templates.default_nameservers.ns2')) }}" required class="cf-input h-9 text-xs font-mono">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nameserver 3 (Optional)</label>
                <input type="text" name="ns3" value="{{ old('ns3', $ns[2] ?? '') }}" class="cf-input h-9 text-xs font-mono">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nameserver 4 (Optional)</label>
                <input type="text" name="ns4" value="{{ old('ns4', $ns[3] ?? '') }}" class="cf-input h-9 text-xs font-mono">
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer">
                    Save Nameservers
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
