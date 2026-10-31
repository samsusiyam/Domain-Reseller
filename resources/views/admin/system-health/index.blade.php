@extends('layouts.admin', ['title' => 'System Health & Infrastructure'])

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">System Health & Status</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Live diagnostics for database performance, server metrics, cron jobs, and upstream registrar connectivity.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.system-health.backup') }}" method="POST">
                @csrf
                <button type="submit" class="cf-btn cf-btn-primary h-8 px-3 text-xs flex items-center gap-1.5 cursor-pointer font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    <span>Create Database Backup</span>
                </button>
            </form>
            <button onclick="window.location.reload()" class="cf-btn cf-btn-secondary h-8 px-3 text-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>Refresh Metrics</span>
            </button>
        </div>
    </div>

    <!-- Status Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Database -->
        <div class="cf-panel p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Database</span>
                <span class="w-2 h-2 rounded-full {{ $dbStatus ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
            </div>
            <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $dbStatus ? 'Connected' : 'Error' }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                <span>Latency: {{ $dbLatency ?? 'N/A' }} ms</span>
                <span>{{ $dbSizeMb }} MB ({{ $dbTableCount }} tables)</span>
            </div>
        </div>

        <!-- Crontab Scheduler -->
        <div class="cf-panel p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cron Scheduler</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <div class="text-lg font-bold text-slate-900 dark:text-white">Active</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Every 1 min (schedule:run)
            </div>
        </div>

        <!-- PHP Engine -->
        <div class="cf-panel p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">PHP Version</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400">ea-php84</span>
            </div>
            <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $serverInfo['php_version'] }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Memory: {{ $serverInfo['memory_limit'] }} • Max: {{ $serverInfo['max_execution_time'] }}
            </div>
        </div>

        <!-- Storage -->
        <div class="cf-panel p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Disk Space</span>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">{{ $diskPercent }}%</span>
            </div>
            <div class="text-lg font-bold text-slate-900 dark:text-white">{{ round($diskFree / 1024 / 1024 / 1024, 1) }} GB free</div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-2 overflow-hidden">
                <div class="bg-[#F6821F] h-full rounded-full" style="width: {{ $diskPercent }}%"></div>
            </div>
        </div>
    </div>

    <!-- Upstream Registrars Connectivity Ping -->
    <div class="cf-panel p-6">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Upstream Registrar API Ping</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Real-time HTTP health check against connected registry gateways.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($registrars as $name => $r)
                <div class="p-3.5 rounded-[6px] border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-semibold text-xs text-slate-900 dark:text-white">{{ $name }}</span>
                        @if($r['status'])
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">ONLINE</span>
                        @else
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400">DOWN</span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                        Latency: {{ $r['latency'] ? $r['latency'] . 'ms' : 'Timeout' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Environment & Application Details -->
    <div class="cf-panel p-6">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Environment & Framework</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-500 dark:text-slate-400 block text-[11px]">Application Name</span>
                <span class="font-medium text-slate-900 dark:text-white">{{ config('app.name') }}</span>
            </div>
            <div>
                <span class="text-slate-500 dark:text-slate-400 block text-[11px]">Laravel Framework</span>
                <span class="font-medium text-slate-900 dark:text-white">{{ $serverInfo['laravel_version'] }}</span>
            </div>
            <div>
                <span class="text-slate-500 dark:text-slate-400 block text-[11px]">Environment</span>
                <span class="font-medium text-slate-900 dark:text-white capitalize">{{ $serverInfo['environment'] }}</span>
            </div>
            <div>
                <span class="text-slate-500 dark:text-slate-400 block text-[11px]">Debug Mode</span>
                <span class="font-medium {{ $serverInfo['debug_mode'] ? 'text-amber-500' : 'text-emerald-500' }}">
                    {{ $serverInfo['debug_mode'] ? 'Enabled' : 'Disabled (Secure)' }}
                </span>
            </div>
        </div>
    </div>


    <!-- Database Disaster Recovery & Backups -->
    <div class="cf-panel p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Database Disaster Recovery & Backups</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Automated and on-demand compressed MySQL snapshots (.sql.gz).</p>
            </div>
            <form action="{{ route('admin.system-health.backup') }}" method="POST">
                @csrf
                <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm text-xs cursor-pointer">
                    + Take Snapshot Now
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium">
                    <tr>
                        <th class="px-4 py-3">Archive Filename</th>
                        <th class="px-4 py-3">File Size</th>
                        <th class="px-4 py-3">Created At</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($backups as $b)
                        <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-mono font-medium text-slate-900 dark:text-white">
                                {{ $b['filename'] }}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-500 dark:text-slate-400">
                                {{ $b['size_mb'] }} MB
                            </td>
                            <td class="px-4 py-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                {{ $b['created_at']->format('M d, Y H:i:s') }} ({{ $b['created_at']->diffForHumans() }})
                            </td>
                            <td class="px-4 py-3 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('admin.system-health.backup.download', $b['filename']) }}" class="cf-btn cf-btn-secondary cf-btn-sm text-xs cursor-pointer">
                                    Download
                                </a>
                                <form action="{{ route('admin.system-health.backup.delete', $b['filename']) }}" method="POST" onsubmit="return confirm('Delete this database backup?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm text-xs text-rose-600 hover:text-rose-700 cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                No database snapshots yet. Click "Create Database Backup" above to generate an immediate snapshot.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
