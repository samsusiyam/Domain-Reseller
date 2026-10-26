@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Domain Portfolio Analytics</h1>
        <p class="text-xs text-slate-500 mt-1">Lifecycle distribution, extension popularity, and 6-month domain expiration schedule.</p>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-sm font-semibold overflow-x-auto">
        <a href="{{ route('admin.reports.revenue') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Revenue</a>
        <a href="{{ route('admin.reports.domains') }}" class="px-4 py-2 text-indigo-600 border-b-2 border-indigo-600 whitespace-nowrap">Domains</a>
        <a href="{{ route('admin.reports.clients') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Clients</a>
        <a href="{{ route('admin.reports.resellers') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Resellers</a>
        <a href="{{ route('admin.reports.tlds') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">TLD Margins</a>
        <a href="{{ route('admin.reports.payments') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Gateways</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Status Breakdown -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Status Distribution</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($statusBreakdown as $sb)
                    <div class="flex items-center justify-between text-sm">
                        <span class="capitalize font-semibold text-slate-700">{{ str_replace('_', ' ', $sb->status) }}</span>
                        <span class="font-black text-slate-900">{{ number_format($sb->count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TLD Breakdown -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Top TLD Extensions</h3>
            </div>
            <div class="p-6 space-y-3">
                @foreach($tldBreakdown as $tb)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-mono font-bold text-indigo-700">{{ $tb->tld }}</span>
                        <span class="font-black text-slate-900">{{ number_format($tb->count) }} domains</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 6-Month Expiration Schedule -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Upcoming Expirations</h3>
            </div>
            <div class="p-6 space-y-3">
                @forelse($expiringMonthly as $em)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::createFromFormat('Y-m', $em->month)->format('M Y') }}</span>
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
