@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    inspectModalOpen: false,
    selectedLog: null,
    viewDetails(log) {
        this.selectedLog = log;
        this.inspectModalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">WHMCS & Reseller API Inspector</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Real-time incoming telemetry, endpoint performance, and debugging logs for reseller modules.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.api-logs.clear') }}" method="POST" onsubmit="return confirm('Clear all API logs?');">
                @csrf
                <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium cursor-pointer">
                    Clear Logs
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Today's API Calls</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($stats['total_today']) }}</div>
        </div>
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Failed / Rejected</span>
            <div class="text-2xl font-bold font-mono {{ $stats['errors_today'] > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1 tabular-nums">{{ number_format($stats['errors_today']) }}</div>
        </div>
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Average Latency</span>
            <div class="text-2xl font-bold font-mono text-[#F6821F] mt-1 tabular-nums">{{ $stats['avg_latency_ms'] }} ms</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Filter endpoint or IP address..." class="cf-input h-9 text-xs flex-1 min-w-[200px]">
            <select name="method" class="cf-select h-9 text-xs">
                <option value="">All Methods</option>
                <option value="GET" {{ request('method') === 'GET' ? 'selected' : '' }}>GET</option>
                <option value="POST" {{ request('method') === 'POST' ? 'selected' : '' }}>POST</option>
                <option value="DELETE" {{ request('method') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
            </select>
            <select name="status" class="cf-select h-9 text-xs">
                <option value="">All Statuses</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>2xx Success</option>
                <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>4xx / 5xx Errors</option>
            </select>
            <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm text-xs cursor-pointer">Filter</button>
            @if(request()->hasAny(['search', 'method', 'status']))
                <a href="{{ route('admin.api-logs.index') }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Reset</a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium">
                    <tr>
                        <th class="px-5 py-3.5">Method & Path</th>
                        <th class="px-5 py-3.5">Reseller / User</th>
                        <th class="px-5 py-3.5">Client IP</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Latency</th>
                        <th class="px-5 py-3.5 text-right">Time</th>
                        <th class="px-5 py-3.5 text-right">Inspect</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3 font-mono">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase mr-1.5
                                    {{ $log->method === 'POST' ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="text-slate-900 dark:text-white font-medium">{{ $log->path }}</span>
                            </td>
                            <td class="px-5 py-3">
                                {{ $log->user?->name ?? 'API Key / Guest' }}
                            </td>
                            <td class="px-5 py-3 font-mono text-slate-500 dark:text-slate-400">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold
                                    {{ $log->status_code < 300 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : ($log->status_code < 500 ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300') }}">
                                    {{ $log->status_code }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400">
                                {{ $log->duration_ms }} ms
                            </td>
                            <td class="px-5 py-3 text-right text-slate-400 font-mono text-[11px]">
                                {{ $log->created_at?->diffForHumans() }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button type="button" @click="viewDetails({{ json_encode($log) }})" class="cf-btn cf-btn-secondary cf-btn-sm text-xs cursor-pointer">
                                    Payload
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                                No API requests recorded yet. Incoming WHMCS and reseller API calls will appear here in real time.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Inspector Modal -->
    <div x-show="inspectModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="inspectModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">API Request Payload & Response</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="(selectedLog?.method || '') + ' ' + (selectedLog?.path || '')"></p>
                    </div>
                    <button type="button" @click="inspectModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4 mt-4 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Request Body / Payload</label>
                        <pre class="bg-slate-950 text-slate-200 rounded-lg p-3 font-mono text-[11px] overflow-x-auto max-h-48 whitespace-pre-wrap" x-text="selectedLog?.payload || 'None / Empty Payload'"></pre>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Response Body</label>
                        <pre class="bg-slate-950 text-slate-200 rounded-lg p-3 font-mono text-[11px] overflow-x-auto max-h-48 whitespace-pre-wrap" x-text="selectedLog?.response_body || 'Empty Response'"></pre>
                    </div>
                </div>

                <div class="flex justify-end pt-4 mt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="inspectModalOpen = false" class="cf-btn cf-btn-secondary text-xs">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
