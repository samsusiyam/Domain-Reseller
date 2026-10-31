@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Support Tickets</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Get help with your domains, nameservers, billing, or API integrations.</p>
        </div>
        <div>
            <a href="{{ route('client.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-sm transition text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Open New Ticket
            </a>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        @if($tickets->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-300">No Support Tickets Found</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Need assistance? Open a support ticket and our engineering team will assist you promptly.</p>
                <div class="mt-5">
                    <a href="{{ route('client.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 shadow-sm transition">
                        Open Support Ticket
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Ticket ID</th>
                            <th class="px-6 py-3.5">Subject</th>
                            <th class="px-6 py-3.5">Department</th>
                            <th class="px-6 py-3.5 text-center">Priority</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5">Last Updated</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-800 dark:text-white">
                                    <a href="{{ route('client.tickets.show', $ticket->id) }}" class="text-brand-600 hover:text-brand-800 dark:text-[#F6821F] dark:hover:text-[#ff9d47]">
                                        #{{ substr($ticket->uuid ?? (string)$ticket->id, 0, 8) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('client.tickets.show', $ticket->id) }}" class="font-semibold text-slate-800 dark:text-white hover:text-brand-600 dark:hover:text-[#F6821F] block">
                                        {{ $ticket->subject }}
                                    </a>
                                    @if($ticket->domain)
                                        <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">{{ $ticket->domain->domain_name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 capitalize text-slate-600 dark:text-slate-400 text-xs font-medium">
                                    {{ $ticket->department }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($ticket->priority === 'urgent')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200/50">Urgent</span>
                                    @elseif($ticket->priority === 'high')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/50">High</span>
                                    @elseif($ticket->priority === 'medium')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200/50">Medium</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">Low</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($ticket->status === 'open')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/50">Open</span>
                                    @elseif($ticket->status === 'customer_reply')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200/50">Client Reply</span>
                                    @elseif($ticket->status === 'answered')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-300 border border-indigo-200/50">Answered</span>
                                    @elseif($ticket->status === 'closed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Closed</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/50">{{ ucfirst($ticket->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $ticket->last_reply_at ? $ticket->last_reply_at->diffForHumans() : $ticket->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('client.tickets.show', $ticket->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition">
                                        View Thread
                                    </a>
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
