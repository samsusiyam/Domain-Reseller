@extends('layouts.reseller', ['header' => 'Active Sessions'])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Active Browser & Device Sessions</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage and revoke authenticated login sessions across all your devices.</p>
        </div>
        @if(count($sessions) > 1)
            <form action="{{ route('reseller.sessions.destroy.other') }}" method="POST" onsubmit="return confirm('Revoke all other active sessions?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="h-8 px-3 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/30 text-red-600 hover:bg-red-100 text-xs font-semibold transition cursor-pointer">
                    Revoke Other Sessions
                </button>
            </form>
        @endif
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold overflow-x-auto pb-2">
        <a href="{{ route('reseller.profile.edit') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Profile, Password & Passkeys</a>
        <a href="{{ route('reseller.two-factor.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Two-Factor Authentication</a>
        <a href="{{ route('reseller.sessions.index') }}" class="px-3.5 py-1.5 rounded-lg bg-[#F6821F]/10 text-[#F6821F] font-bold">Active Sessions</a>
    </div>

    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-xs">
        <div class="pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Signed-in Devices</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">All current logged in browser sessions under your reseller account.</p>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($sessions as $sess)
                <div class="py-3.5 flex items-center justify-between gap-4 text-xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $sess->ip_address ?? 'Unknown IP' }}</span>
                                @if($sess->is_current_device)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        Current Device
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-md mt-0.5" title="{{ $sess->user_agent }}">
                                {{ $sess->user_agent ?? 'Unknown User Agent' }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5">
                                Last active: {{ $sess->last_activity->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    @if(!$sess->is_current_device)
                        <form action="{{ route('reseller.sessions.destroy', $sess->id) }}" method="POST" onsubmit="return confirm('Revoke this session?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 text-xs text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded font-medium transition cursor-pointer">
                                Revoke
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="py-6 text-center text-xs text-slate-500">
                    No active sessions recorded in database.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
