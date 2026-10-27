@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">ICANN Compliance & Domain Lifecycle Monitor</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Track domain renewal notices, auto-renew grace periods, RGP redemption states, and ICANN WDRP compliance.</p>
        </div>
        <div>
            <a href="{{ route('admin.domains.index') }}" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
                <span>All Domains</span>
            </a>
        </div>
    </div>

    <!-- 4 Lifecycle Stage Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('admin.domains.lifecycle', ['tab' => 'expiring_30']) }}" class="bg-white dark:bg-[#111827] rounded-xl border {{ $tab === 'expiring_30' ? 'border-[#F6821F]' : 'border-slate-200 dark:border-slate-800' }} p-4 shadow-xs hover:border-[#F6821F] transition">
            <span class="text-[11px] font-medium text-amber-600 dark:text-amber-400 uppercase tracking-wider">Expiring (30 Days)</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($stats['expiring_30']) }}</div>
            <p class="text-[10px] text-slate-400 mt-0.5">ICANN 30d/15d/7d notices</p>
        </a>

        <a href="{{ route('admin.domains.lifecycle', ['tab' => 'grace']) }}" class="bg-white dark:bg-[#111827] rounded-xl border {{ $tab === 'grace' ? 'border-[#F6821F]' : 'border-slate-200 dark:border-slate-800' }} p-4 shadow-xs hover:border-[#F6821F] transition">
            <span class="text-[11px] font-medium text-orange-600 dark:text-orange-400 uppercase tracking-wider">Auto-Renew Grace</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($stats['grace_period']) }}</div>
            <p class="text-[10px] text-slate-400 mt-0.5">Day 1 to 30 past expiry</p>
        </a>

        <a href="{{ route('admin.domains.lifecycle', ['tab' => 'redemption']) }}" class="bg-white dark:bg-[#111827] rounded-xl border {{ $tab === 'redemption' ? 'border-[#F6821F]' : 'border-slate-200 dark:border-slate-800' }} p-4 shadow-xs hover:border-[#F6821F] transition">
            <span class="text-[11px] font-medium text-rose-600 dark:text-rose-400 uppercase tracking-wider">Redemption (RGP)</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($stats['redemption']) }}</div>
            <p class="text-[10px] text-slate-400 mt-0.5">Day 31 to 60 (Restore fee)</p>
        </a>

        <a href="{{ route('admin.domains.lifecycle', ['tab' => 'pending_delete']) }}" class="bg-white dark:bg-[#111827] rounded-xl border {{ $tab === 'pending_delete' ? 'border-[#F6821F]' : 'border-slate-200 dark:border-slate-800' }} p-4 shadow-xs hover:border-[#F6821F] transition">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Pending Delete</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($stats['pending_delete']) }}</div>
            <p class="text-[10px] text-slate-400 mt-0.5">Dropping / Non-restorable</p>
        </a>
    </div>

    <!-- Filter & Tabs -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto">
            <a href="{{ route('admin.domains.lifecycle', ['tab' => 'all']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $tab === 'all' ? 'bg-[#F6821F] text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                All Lifecycle Domains
            </a>
            <a href="{{ route('admin.domains.lifecycle', ['tab' => 'expiring_30']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $tab === 'expiring_30' ? 'bg-[#F6821F] text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Expiring (30d)
            </a>
            <a href="{{ route('admin.domains.lifecycle', ['tab' => 'grace']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $tab === 'grace' ? 'bg-[#F6821F] text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Grace Period
            </a>
            <a href="{{ route('admin.domains.lifecycle', ['tab' => 'redemption']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $tab === 'redemption' ? 'bg-[#F6821F] text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Redemption (RGP)
            </a>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search domain or registrant..." class="cf-input h-9 text-xs w-64">
            <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm text-xs cursor-pointer">Search</button>
        </form>
    </div>

    <!-- Domains Table -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium">
                    <tr>
                        <th class="px-5 py-3.5">Domain Name</th>
                        <th class="px-5 py-3.5">Registrant</th>
                        <th class="px-5 py-3.5">Upstream Registrar</th>
                        <th class="px-5 py-3.5">Expires At</th>
                        <th class="px-5 py-3.5 text-center">Lifecycle Phase</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($domains as $domain)
                        @php
                            $diff = $domain->expires_at ? now()->diffInDays($domain->expires_at, false) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('admin.domains.show', $domain->id) }}" class="hover:text-[#F6821F]">
                                    {{ $domain->domain_name }}
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ $domain->user?->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $domain->user?->email }}</div>
                            </td>
                            <td class="px-5 py-3 font-mono text-slate-600 dark:text-slate-400">
                                {{ $domain->registrar?->name ?? 'Default' }}
                            </td>
                            <td class="px-5 py-3 font-mono">
                                <div>{{ $domain->expires_at?->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-[10px] {{ $diff > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $diff > 0 ? "Expires in {$diff} days" : abs((int)$diff) . " days expired" }}
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($diff > 30)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        Active
                                    </span>
                                @elseif($diff > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                        Expiring Soon
                                    </span>
                                @elseif($diff >= -30)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-300">
                                        Grace Period
                                    </span>
                                @elseif($diff >= -60)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                                        Redemption (RGP)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800">
                                        Pending Delete
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <form action="{{ route('admin.domains.lifecycle.notice', $domain->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm text-xs cursor-pointer">
                                        Send ICANN Notice
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">
                                No domains in this lifecycle phase.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($domains->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $domains->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
