@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.activity-logs.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Audit Log Entry #{{ $log->id }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Recorded on {{ $log->created_at->format('M d, Y H:i:s') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 uppercase font-semibold block">Actor / User</span>
                <span class="font-bold text-slate-900 text-sm">{{ $log->user?->name ?? 'System Automated' }}</span>
                <span class="text-slate-500 font-mono block">{{ $log->user?->email }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold block">Origin IP Address</span>
                <span class="font-mono font-bold text-slate-900 text-sm">{{ $log->ip_address ?? '127.0.0.1' }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold block">Log Category</span>
                <span class="font-bold text-indigo-700 text-sm font-mono">{{ $log->log_name ?? 'activity' }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold block">Action Event</span>
                <span class="font-bold text-slate-800 text-sm font-mono">{{ $log->description }}</span>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Logged Properties & Attributes (JSON)</h4>
            <div class="bg-slate-950 text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto">
                <pre>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
