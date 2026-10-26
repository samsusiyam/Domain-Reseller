@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.domains.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $domain->domain_name }}</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $domain->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                        {{ ucfirst($domain->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Domain ID: #{{ $domain->id }} &bull; Upstream: <strong>{{ $domain->registrar?->name ?? 'Default' }}</strong></p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Sync Upstream -->
            <form action="{{ route('admin.domains.sync', $domain->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync Registrar
                </button>
            </form>

            <a href="{{ route('admin.domains.edit', $domain->id) }}" class="px-3 py-1.5 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-sm transition">
                Edit Domain
            </a>
        </div>
    </div>

    <!-- Management Controls Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-wrap items-center gap-3">
        <!-- Toggle Lock -->
        <form action="{{ route('admin.domains.toggle-lock', $domain->id) }}" method="POST">
            @csrf
            <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $domain->is_locked ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                {{ $domain->is_locked ? '🔒 Transfer Lock: ON' : '🔓 Transfer Lock: OFF' }}
            </button>
        </form>

        <!-- Toggle Privacy -->
        <form action="{{ route('admin.domains.toggle-privacy', $domain->id) }}" method="POST">
            @csrf
            <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $domain->is_private ? 'bg-indigo-100 text-indigo-800 hover:bg-indigo-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {{ $domain->is_private ? '🛡️ WHOIS Privacy: ON' : '🛡️ WHOIS Privacy: OFF' }}
            </button>
        </form>

        <!-- Get EPP -->
        <form action="{{ route('admin.domains.get-epp', $domain->id) }}" method="POST">
            @csrf
            <button type="submit" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition">
                🔑 Fetch EPP Code
            </button>
        </form>

        <!-- Renew 1 Year -->
        <form action="{{ route('admin.domains.renew', $domain->id) }}" method="POST" onsubmit="return confirm('Renew this domain for 1 year upstream?');">
            @csrf
            <input type="hidden" name="years" value="1">
            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                + Renew 1 Year
            </button>
        </form>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Attributes Card -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Domain Attributes</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 text-xs">
                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Registered Owner</span>
                    @if($domain->user)
                        <a href="{{ $domain->user->isReseller() ? route('admin.resellers.show', $domain->user->id) : route('admin.clients.show', $domain->user->id) }}" class="text-indigo-600 hover:underline font-bold text-sm block">
                            {{ $domain->user->name }}
                        </a>
                        <span class="text-slate-400">{{ $domain->user->email }}</span>
                    @else
                        <span class="text-slate-500">Unassigned</span>
                    @endif
                </div>

                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Registration Date</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ $domain->registration_date ? $domain->registration_date->format('M d, Y') : '—' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Expiration Date</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $domain->expires_at ? $domain->expires_at->format('M d, Y') : '—' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Auto-Renewal</span>
                    <span class="font-semibold {{ $domain->auto_renew ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $domain->auto_renew ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Upstream Order ID</span>
                    <span class="font-mono text-slate-700">{{ $domain->registrar_domain_id ?: '—' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Auth / EPP Code</span>
                    <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded select-all font-bold text-slate-800">
                        {{ $domain->epp_code ?: 'Request above' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Nameservers Card -->
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Authoritative Nameservers</h3>
            <form action="{{ route('admin.domains.nameservers', $domain->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-0.5">Nameserver 1</label>
                    <input type="text" name="ns1" value="{{ old('ns1', $domain->ns1) }}" required class="w-full px-2.5 py-1.5 font-mono text-xs border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-0.5">Nameserver 2</label>
                    <input type="text" name="ns2" value="{{ old('ns2', $domain->ns2) }}" required class="w-full px-2.5 py-1.5 font-mono text-xs border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-0.5">Nameserver 3 (Optional)</label>
                    <input type="text" name="ns3" value="{{ old('ns3', $domain->ns3) }}" class="w-full px-2.5 py-1.5 font-mono text-xs border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-0.5">Nameserver 4 (Optional)</label>
                    <input type="text" name="ns4" value="{{ old('ns4', $domain->ns4) }}" class="w-full px-2.5 py-1.5 font-mono text-xs border border-slate-300 rounded-lg">
                </div>
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition">
                    Save Nameservers Upstream
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
