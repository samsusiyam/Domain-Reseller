@extends('layouts.app', ['title' => 'My Domains', 'header' => 'My Domains'])

@section('content')
<div class="space-y-6">
    <!-- Header with Search & Register CTA -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('client.domains.index') }}" class="max-w-md w-full">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search domain names..."
                       class="cf-input h-10 pl-9 text-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('client.domains.search') }}" class="cf-btn cf-btn-primary cf-btn-sm font-medium text-xs inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Register Domain</span>
            </a>
            <a href="{{ route('domain.transfer') }}" class="cf-btn cf-btn-secondary cf-btn-sm font-medium text-xs inline-flex items-center gap-1.5 cursor-pointer">
                <span>Transfer In</span>
            </a>
        </div>
    </div>

    <!-- Domains Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
        @if($domains->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-5">Domain Name</th>
                            <th class="py-3 px-5">Registration Date</th>
                            <th class="py-3 px-5">Expiration Date</th>
                            <th class="py-3 px-5 text-center">Auto-Renew</th>
                            <th class="py-3 px-5 text-center">Status</th>
                            <th class="py-3 px-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @foreach($domains as $domain)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-5 font-semibold text-slate-900 dark:text-white">
                                    <a href="{{ route('client.domains.show', $domain->id) }}" class="hover:text-[#F6821F] transition">
                                        {{ $domain->domain_name }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-5 text-slate-500 dark:text-slate-400">
                                    {{ $domain->registered_at ? $domain->registered_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-500 dark:text-slate-400">
                                    {{ $domain->expires_at ? $domain->expires_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $domain->auto_renew ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                        {{ $domain->auto_renew ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                        {{ $domain->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' }}">
                                        {{ ucfirst($domain->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <a href="{{ route('client.domains.show', $domain->id) }}"
                                       class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium inline-flex items-center gap-1 cursor-pointer">
                                        <span>Manage</span>
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $domains->links() }}
            </div>
        @else
            <!-- Actionable Empty State -->
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-950/30 border border-orange-100 dark:border-orange-900/50 flex items-center justify-center text-[#F6821F] mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">No domains registered yet</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Search and register your first domain name at direct wholesale rates or transfer an existing portfolio.</p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <a href="{{ route('client.domains.search') }}" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-medium">
                        Search Domains
                    </a>
                    <a href="{{ route('domain.transfer') }}" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium">
                        Transfer In Domain
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
