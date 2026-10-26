@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">System Audit & Activity Trail</h1>
            <p class="text-xs text-slate-500 mt-1">Immutable security log of administrative actions, API calls, user registrations, and billing modifications.</p>
        </div>
        <div>
            <form action="{{ route('admin.activity-logs.clear-old') }}" method="POST" onsubmit="return confirm('Prune logs older than 90 days?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-sm transition">
                    Prune Logs (&gt; 90 Days)
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by description, IP address, user email..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Search Logs
            </button>
            @if(request('search'))
                <a href="{{ route('admin.activity-logs.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($logs->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No activity logs recorded.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Timestamp</th>
                            <th class="px-6 py-3.5">User / Actor</th>
                            <th class="px-6 py-3.5">IP Address</th>
                            <th class="px-6 py-3.5">Event Description</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-sans text-slate-500 whitespace-nowrap">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 font-sans">
                                    <div class="font-bold text-slate-900">{{ $log->user?->name ?? 'System/Guest' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $log->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $log->ip_address ?? '—' }}</td>
                                <td class="px-6 py-4 font-sans text-slate-700">
                                    {{ $log->description }}
                                </td>
                                <td class="px-6 py-4 text-right font-sans">
                                    <a href="{{ route('admin.activity-logs.show', $log->id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs">
                                        Payload &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-100 font-sans">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
