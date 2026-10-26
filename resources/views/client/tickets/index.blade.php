@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Support Tickets</h1>
            <p class="text-sm text-slate-500 mt-1">Get help with your domains, nameservers, billing, or API integrations.</p>
        </div>
        <div>
            <a href="{{ route('client.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-sm transition text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Open New Ticket
            </a>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($tickets->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700">No Support Tickets Found</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Need assistance? Open a support ticket and our engineering team will assist you promptly.</p>
                <div class="mt-5">
                    <a href="{{ route('client.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 shadow-sm transition">
                        Open Support Ticket
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
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
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-800">
                                    <a href="{{ route('client.tickets.show', $ticket->id) }}" class="text-brand-600 hover:text-brand-800">
                                        #{{ substr($ticket->uuid ?? (string)$ticket->id, 0, 8) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('client.tickets.show', $ticket->id) }}" class="font-semibold text-slate-800 hover:text-brand-600 block">
                                        {{ $ticket->subject }}
                                    </a>
                                    @if($ticket->domain)
                                        <span class="text-xs text-slate-400 font-mono">{{ $ticket->domain->domain_name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 capitalize text-slate-600 text-xs font-medium">
                                    {{ $ticket->department }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($ticket->priority === 'urgent')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Urgent</span>
                                    @elseif($ticket->priority === 'high')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">High</span>
                                    @elseif($ticket->priority === 'medium')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Medium</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Low</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($ticket->status === 'open')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Open</span>
                                    @elseif($ticket->status === 'customer_reply')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Client Reply</span>
                                    @elseif($ticket->status === 'answered')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">Answered</span>
                                    @elseif($ticket->status === 'closed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Closed</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">{{ ucfirst($ticket->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">
                                    {{ $ticket->last_reply_at ? $ticket->last_reply_at->diffForHumans() : $ticket->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('client.tickets.show', $ticket->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                        View Thread
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $tickets->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
