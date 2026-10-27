@extends('layouts.admin', ['title' => 'Client Accounts', 'header' => 'Clients'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Client Accounts</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage direct retail user profiles, portfolio scopes, and financial balances</p>
        </div>
        <div>
            <a href="{{ route('admin.clients.create') }}" class="cf-btn cf-btn-primary text-xs inline-flex items-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Client
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, company, phone..."
                    class="cf-input w-full pl-9 text-xs">
            </div>

            <div class="w-full sm:w-48">
                <select name="status" class="cf-input w-full text-xs font-semibold">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="cf-btn cf-btn-secondary text-xs px-4">
                    Filter
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.clients.index') }}" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 underline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Clients Table -->
    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 overflow-hidden">
        @if($clients->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-xs">
                No client accounts found matching the criteria.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/40 text-[11px] uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3">Client Details</th>
                            <th class="px-5 py-3">Company / Org</th>
                            <th class="px-5 py-3 text-center">Domains</th>
                            <th class="px-5 py-3 text-right">Wallet Balance</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-normal">
                        @foreach($clients as $c)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('admin.clients.show', $c->id) }}" class="font-bold text-slate-900 dark:text-white hover:text-[#F6821F] transition block">
                                        {{ $c->name }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $c->email }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">
                                    {{ $c->company_name ?: '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-bold text-slate-800 dark:text-slate-200">
                                    {{ $c->domains_count }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    ${{ number_format($c->walletBalance, 2) }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-medium uppercase
                                        {{ $c->status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' }}">
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-2">
                                    <a href="{{ route('admin.clients.show', $c->id) }}" class="text-[#F6821F] hover:underline text-xs font-semibold">Manage</a>
                                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                                    <a href="{{ route('admin.clients.edit', $c->id) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $clients->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
