@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Domain Portfolio Analytics</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lifecycle distribution, extension popularity, and 6-month domain expiration schedule.</p>
    </div>

    <x-admin-reports-tabs active="domains" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Status Breakdown -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Status Distribution</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($statusBreakdown as $sb)
                    <div class="flex items-center justify-between text-sm">
                        <span class="capitalize font-semibold text-slate-700 dark:text-slate-300">{{ str_replace('_', ' ', $sb->status) }}</span>
                        <span class="font-black text-slate-900 dark:text-white">{{ number_format($sb->count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TLD Breakdown -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Top TLD Extensions</h3>
            </div>
            <div class="p-6 space-y-3">
                @foreach($tldBreakdown as $tb)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-mono font-bold text-[#F6821F]">{{ $tb->tld }}</span>
                        <span class="font-black text-slate-900 dark:text-white">{{ number_format($tb->count) }} domains</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 6-Month Expiration Schedule -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Upcoming Expirations</h3>
            </div>
            <div class="p-6 space-y-3">
                @forelse($expiringMonthly as $em)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::createFromFormat('Y-m', $em->month)->format('M Y') }}</span>
                        <span class="font-bold text-amber-600">{{ number_format($em->count) }} expiring</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">No active domains expiring in next 6 months.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
