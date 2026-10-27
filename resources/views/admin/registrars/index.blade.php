@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Upstream Registrar Connectors</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage live API integrations to wholesale registry providers and sync real-time credit balances.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-medium text-slate-600 dark:text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ $registrars->where('is_active', true)->count() }} Active Modules</span>
            </span>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($registrars as $reg)
            <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-5 flex flex-col justify-between shadow-xs hover:border-[#F6821F]/40 transition">
                <div>
                    <!-- Top: Avatar + Title + Status -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-950/40 border border-orange-100 dark:border-orange-900/50 flex items-center justify-center font-bold text-[#F6821F] text-xs">
                                {{ strtoupper(substr($reg->slug ?? $reg->name, 0, 3)) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $reg->name }}</h3>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400">
                                        {{ $reg->environment }}
                                    </span>
                                    @if($reg->is_default)
                                        <span class="text-[10px] font-semibold text-[#F6821F] bg-orange-50 dark:bg-orange-950/40 px-1.5 py-0.5 rounded">
                                            Default
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium
                            {{ $reg->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60' : 'bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $reg->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $reg->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </div>

                    <!-- Balance Row -->
                    <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Account Balance</span>
                        <div class="text-right">
                            <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">
                                ${{ number_format((float) ($reg->balance ?? 0), 2) }}
                            </span>
                            @if((float)($reg->balance ?? 0) < (float)($reg->low_balance_threshold ?? 50) && $reg->is_active)
                                <span class="block text-[10px] text-amber-600 dark:text-amber-400 font-medium">Low balance warning</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Actions -->
                <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                    <form action="{{ route('admin.registrars.sync-balance', $reg->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                title="Sync live balance from API"
                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium inline-flex items-center gap-1 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Sync</span>
                        </button>
                    </form>

                    <a href="{{ route('admin.registrars.edit', $reg->id) }}"
                       class="cf-btn cf-btn-secondary cf-btn-sm font-medium text-xs inline-flex items-center gap-1 cursor-pointer">
                        <span>Configure API</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
