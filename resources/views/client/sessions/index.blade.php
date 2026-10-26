@extends('layouts.app', ['title' => 'Browser Sessions', 'header' => 'Active Sessions'])

@section('content')
<div class="max-w-4xl space-y-8">
    <!-- Sub-navigation tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 text-sm font-semibold">
        <a href="{{ route('client.profile.edit') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">Personal Details</a>
        <a href="{{ route('client.two-factor.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">Two-Factor Authentication</a>
        <a href="{{ route('client.sessions.index') }}" class="px-3.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-600">Browser Sessions</a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 mb-1">Active Device Sessions</h3>
        <p class="text-xs text-slate-500 mb-6">Review devices currently signed in to your portal. You can revoke any unfamiliar sessions.</p>

        <div class="divide-y divide-slate-100">
            @forelse($sessions as $session)
                <div class="py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-800">{{ $session->ip_address }}</span>
                                @if($session->is_current_device)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-700">This Device</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 truncate max-w-md">{{ $session->user_agent }}</p>
                            <span class="text-[11px] text-slate-400">Last Active: {{ $session->last_activity->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if(!$session->is_current_device)
                        <form action="{{ route('client.sessions.destroy', $session->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs rounded-xl transition">
                                Revoke
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="py-8 text-center text-xs text-slate-400">No active sessions found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
