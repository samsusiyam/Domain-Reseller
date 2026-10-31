@extends('layouts.admin', ['title' => $domain->domain_name, 'header' => 'Domain Overview'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.domains.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $domain->domain_name }}</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium uppercase
                        {{ $domain->status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' }}">
                        {{ ucfirst($domain->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Domain ID: #{{ $domain->id }} &bull; Upstream Registrar: <strong class="text-slate-900 dark:text-white">{{ $domain->registrar?->name ?? 'Default' }}</strong>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Sync Upstream -->
            <form action="{{ route('admin.domains.sync', $domain->id) }}" method="POST">
                @csrf
                <button type="submit" class="cf-btn cf-btn-secondary text-xs inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync Registrar
                </button>
            </form>

            <a href="{{ route('admin.domains.edit', $domain->id) }}" class="cf-btn cf-btn-secondary text-xs">
                Edit Domain
            </a>
        </div>
    </div>

    <!-- Management Controls Bar -->
    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 p-4 flex flex-wrap items-center gap-3">
        <!-- Toggle Lock -->
        <form action="{{ route('admin.domains.toggle-lock', $domain->id) }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer border {{ $domain->is_locked ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20 hover:bg-rose-500/20' }}">
                {{ $domain->is_locked ? '🔒 Transfer Lock: ON' : '🔓 Transfer Lock: OFF' }}
            </button>
        </form>

        <!-- Toggle Privacy -->
        <form action="{{ route('admin.domains.toggle-privacy', $domain->id) }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer border {{ $domain->is_private ? 'bg-[#F6821F]/10 text-[#F6821F] border-[#F6821F]/20 hover:bg-[#F6821F]/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200' }}">
                {{ $domain->is_private ? '🛡️ WHOIS Privacy: ON' : '🛡️ WHOIS Privacy: OFF' }}
            </button>
        </form>

        <!-- Get EPP -->
        <form action="{{ route('admin.domains.get-epp', $domain->id) }}" method="POST">
            @csrf
            <button type="submit" class="cf-btn cf-btn-secondary text-xs inline-flex items-center gap-1 cursor-pointer">
                🔑 Fetch EPP Code
            </button>
        </form>

        <!-- Renew 1 Year -->
        <form action="{{ route('admin.domains.renew', $domain->id) }}" method="POST" onsubmit="return confirm('Renew this domain for 1 year upstream?');">
            @csrf
            <input type="hidden" name="years" value="1">
            <button type="submit" class="cf-btn cf-btn-primary text-xs cursor-pointer">
                + Renew 1 Year
            </button>
        </form>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Attributes Card -->
        <div class="lg:col-span-2 bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-slate-800">
                Domain Attributes
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 text-xs">
                <div>
                    <span class="text-slate-500 dark:text-slate-400 block font-medium uppercase text-[11px] mb-1">Registered Owner</span>
                    @if($domain->user)
                        <a href="{{ $domain->user->isReseller() ? route('admin.resellers.show', $domain->user->id) : route('admin.clients.show', $domain->user->id) }}" class="text-[#F6821F] hover:underline font-bold text-sm block">
                            {{ $domain->user->name }}
                        </a>
                        <span class="text-slate-400 text-[11px] font-mono">{{ $domain->user->email }}</span>
                    @else
                        <span class="text-slate-400">Unassigned</span>
                    @endif
                </div>

                <div>
                    <span class="text-slate-500 dark:text-slate-400 block font-medium uppercase text-[11px] mb-1">Registration Date</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-sm">
                        {{ $domain->registered_at ? $domain->registered_at->format('M d, Y') : ($domain->registration_date ? $domain->registration_date->format('M d, Y') : '—') }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 dark:text-slate-400 block font-medium uppercase text-[11px] mb-1">Expiration Date</span>
                    <span class="font-bold text-slate-900 dark:text-white text-sm">
                        {{ $domain->expires_at ? $domain->expires_at->format('M d, Y') : '—' }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 dark:text-slate-400 block font-medium uppercase text-[11px] mb-1">Auto-Renewal</span>
                    <span class="font-semibold {{ $domain->is_auto_renew || $domain->auto_renew ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                        {{ $domain->is_auto_renew || $domain->auto_renew ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 dark:text-slate-400 block font-medium uppercase text-[11px] mb-1">Upstream Order ID</span>
                    <span class="font-mono text-slate-700 dark:text-slate-300 text-xs">
                        {{ $domain->registrar_domain_id ?: ($domain->registrar_order_id ?: '—') }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 dark:text-slate-400 block font-medium uppercase text-[11px] mb-1">Auth / EPP Code</span>
                    <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded select-all font-semibold text-slate-900 dark:text-slate-100">
                        {{ $domain->auth_code ?: ($domain->epp_code ?: 'Request above') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Nameservers Card -->
        <div class="lg:col-span-1 bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-slate-800">
                Authoritative Nameservers
            </h3>
            <form action="{{ route('admin.domains.nameservers', $domain->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">Nameserver 1</label>
                    <input type="text" name="ns1" value="{{ old('ns1', $domain->nameservers[0] ?? $domain->ns1) }}" required class="cf-input w-full font-mono text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">Nameserver 2</label>
                    <input type="text" name="ns2" value="{{ old('ns2', $domain->nameservers[1] ?? $domain->ns2) }}" required class="cf-input w-full font-mono text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">Nameserver 3 (Optional)</label>
                    <input type="text" name="ns3" value="{{ old('ns3', $domain->nameservers[2] ?? $domain->ns3) }}" class="cf-input w-full font-mono text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">Nameserver 4 (Optional)</label>
                    <input type="text" name="ns4" value="{{ old('ns4', $domain->nameservers[3] ?? $domain->ns4) }}" class="cf-input w-full font-mono text-xs">
                </div>
                <button type="submit" class="cf-btn cf-btn-primary w-full text-xs shadow-xs cursor-pointer">
                    Save Nameservers Upstream
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
