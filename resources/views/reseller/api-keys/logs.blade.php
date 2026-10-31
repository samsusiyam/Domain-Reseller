@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reseller.api-keys.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">API Access Logs</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">Audit trail of REST API calls made with your API credentials.</p>
        </div>
        <div>
            <a href="{{ route('reseller.api-docs') }}" class="px-4 py-2 bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-900/40 text-[#F6821F] hover:bg-orange-100 dark:bg-orange-950/40 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                API Reference
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('reseller.api-logs') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by endpoint, domain, or IP address..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F]">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Filter Logs
            </button>
            @if(request('search'))
                <a href="{{ route('reseller.api-logs') }}" class="text-xs text-slate-500 hover:text-slate-800 dark:text-slate-200 underline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        @if($logs->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700">No API Logs Recorded</h3>
                <p class="text-sm text-slate-500 mt-1">API calls made using your keys will appear here with timing, caller IP, and responses.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Timestamp</th>
                            <th class="px-6 py-3.5">Caller IP</th>
                            <th class="px-6 py-3.5">Endpoint / Action</th>
                            <th class="px-6 py-3.5">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap font-sans">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-700 font-bold whitespace-nowrap">
                                    {{ $log->ip_address ?? '—' }}
                                </td>
                                <td class="px-6 py-3.5 text-[#F6821F] font-semibold">
                                    {{ $log->description }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-500 max-w-md truncate">
                                    {{ is_array($log->properties) ? json_encode($log->properties) : ($log->properties ?? '—') }}
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
