@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Support Ticket Helpdesk</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Review and resolve customer technical support, domain provisioning, and billing tickets.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets by subject, ticket #, customer name..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="w-full sm:w-36">
                <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="customer_reply" {{ request('status') === 'customer_reply' ? 'selected' : '' }}>Client Reply</option>
                    <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div class="w-full sm:w-36">
                <select name="priority" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Filter
            </button>
            @if(request('search') || request('status') || request('priority'))
                <a href="{{ route('admin.tickets.index') }}" class="text-xs text-slate-500 hover:text-slate-800 dark:text-white underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        @if($tickets->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No tickets found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Ticket #</th>
                            <th class="px-6 py-3.5">Subject</th>
                            <th class="px-6 py-3.5">Customer</th>
                            <th class="px-6 py-3.5">Department</th>
                            <th class="px-6 py-3.5 text-center">Priority</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($tickets as $t)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white text-xs">
                                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                        #{{ substr($t->uuid ?? (string)$t->id, 0, 8) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 block">
                                        {{ $t->subject }}
                                    </a>
                                    @if($t->domain)
                                        <span class="text-xs text-slate-400 font-mono">{{ $t->domain->domain_name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $t->user?->name ?? 'Guest' }}</div>
                                    <div class="text-xs text-slate-400">{{ $t->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4 capitalize text-slate-600 dark:text-slate-300 text-xs font-semibold">
                                    {{ $t->department }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                        {{ $t->priority === 'urgent' ? 'bg-rose-100 text-rose-800' :
                                           ($t->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700 dark:text-slate-300') }}">
                                        {{ $t->priority }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        {{ $t->status === 'open' ? 'bg-emerald-100 text-emerald-800' :
                                           ($t->status === 'customer_reply' ? 'bg-blue-100 text-blue-800' :
                                           ($t->status === 'answered' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-600 dark:text-slate-300')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Reply &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $tickets->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
