@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">API Usage Analytics</h1>
            <p class="text-sm text-slate-500 mt-1">Real-time statistics on automated API transactions and traffic volume.</p>
        </div>
        <div>
            <a href="{{ route('reseller.api-logs') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition">
                View Detailed Logs
            </a>
        </div>
    </div>

    <!-- Metric Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total API Calls</p>
                <p class="text-3xl font-black text-slate-900 mt-0.5">{{ number_format($totalCalls) }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Logs Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800">Latest 20 API Requests</h3>
        </div>

        @if($recentLogs->isEmpty())
            <div class="text-center py-12 px-4">
                <p class="text-sm text-slate-500">No API activity logged yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-sans uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Timestamp</th>
                            <th class="px-6 py-3.5">IP Address</th>
                            <th class="px-6 py-3.5">Action / Endpoint</th>
                            <th class="px-6 py-3.5">Parameters</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentLogs as $log)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-3 font-sans text-slate-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-6 py-3 font-bold text-slate-800">{{ $log->ip_address ?? '—' }}</td>
                                <td class="px-6 py-3 text-indigo-700 font-semibold">{{ $log->description }}</td>
                                <td class="px-6 py-3 text-slate-500 max-w-sm truncate">{{ is_array($log->properties) ? json_encode($log->properties) : ($log->properties ?? '—') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
