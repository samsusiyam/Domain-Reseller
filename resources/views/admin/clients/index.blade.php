@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Client Accounts</h1>
            <p class="text-xs text-slate-500 mt-1">Manage retail retail end-users, view portfolio details, and adjust account balances.</p>
        </div>
        <div>
            <a href="{{ route('admin.clients.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition text-xs">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Client
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, company, phone..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="w-full sm:w-48">
                <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.clients.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($clients->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No client accounts found matching the criteria.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Client Details</th>
                            <th class="px-6 py-3.5">Company / Org</th>
                            <th class="px-6 py-3.5 text-center">Domains</th>
                            <th class="px-6 py-3.5 text-right">Wallet Balance</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($clients as $c)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.clients.show', $c->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 block">
                                        {{ $c->name }}
                                    </a>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $c->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ $c->company_name ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-800">
                                    {{ $c->domains_count }}
                                </td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    ${{ number_format($c->walletBalance, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $c->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.clients.show', $c->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Manage</a>
                                    <a href="{{ route('admin.clients.edit', $c->id) }}" class="text-slate-500 hover:text-slate-800 text-xs font-semibold">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $clients->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
