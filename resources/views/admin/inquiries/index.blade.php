@extends('layouts.admin', ['title' => 'Contact Inquiries'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Contact Inquiries</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Manage customer and reseller inquiries sent through the public contact form.
                @if($unreadCount > 0)
                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FFF7ED] text-[#F6821F] border border-[#FFEDD5]">
                        {{ $unreadCount }} unread
                    </span>
                @endif
            </p>
        </div>

        <form method="GET" action="{{ route('admin.inquiries.index') }}" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="cf-input h-8 text-xs">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
                <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Replied</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search inquiries..." class="cf-input h-8 text-xs w-48">
            <button type="submit" class="cf-btn cf-btn-secondary h-8 px-3 text-xs">Filter</button>
        </form>
    </div>

    @if(session('success'))
        <div class="p-3.5 rounded-[6px] text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/50 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="cf-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="cf-table w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Sender</th>
                        <th class="py-3 px-4">Subject & Message</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Received</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($inquiries as $inq)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors {{ $inq->status === 'unread' ? 'font-medium bg-orange-50/20 dark:bg-orange-950/10' : '' }}">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $inq->name }}</div>
                                <div class="text-slate-500 text-[11px]">{{ $inq->email }}</div>
                                @if($inq->phone)
                                    <div class="text-slate-400 text-[10px] font-mono">{{ $inq->phone }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 max-w-md">
                                <div class="font-medium text-slate-900 dark:text-white mb-1">{{ $inq->subject }}</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px] line-clamp-2 leading-relaxed">
                                    {{ $inq->message }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($inq->status === 'unread')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/50">
                                        Unread
                                    </span>
                                @elseif($inq->status === 'replied')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/50">
                                        Replied
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                                        Archived
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                                {{ $inq->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.inquiries.status', $inq) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($inq->status === 'unread')
                                            <input type="hidden" name="status" value="replied">
                                            <button type="submit" title="Mark as replied" class="cf-btn cf-btn-secondary h-7 px-2.5 text-[11px]">Mark Replied</button>
                                        @else
                                            <input type="hidden" name="status" value="unread">
                                            <button type="submit" title="Mark as unread" class="cf-btn cf-btn-secondary h-7 px-2.5 text-[11px]">Mark Unread</button>
                                        @endif
                                    </form>

                                    <a href="mailto:{{ $inq->email }}?subject=Re: {{ rawurlencode($inq->subject) }}" class="cf-btn cf-btn-primary h-7 px-2.5 text-[11px] flex items-center gap-1">
                                        Reply
                                    </a>

                                    <form action="{{ route('admin.inquiries.destroy', $inq) }}" method="POST" onsubmit="return confirm('Delete this inquiry?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500 text-xs">
                                No inquiries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inquiries->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $inquiries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
