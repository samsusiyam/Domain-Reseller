@extends('layouts.app')

@section('title', 'Ticket #' . substr($ticket->uuid ?? (string)$ticket->id, 0, 8) . ' - ' . $ticket->subject)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('client.tickets.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400">#{{ substr($ticket->uuid ?? (string)$ticket->id, 0, 8) }}</span>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $ticket->subject }}</h1>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <span class="capitalize">Dept: <strong class="text-slate-700 dark:text-slate-300">{{ $ticket->department }}</strong></span>
                    <span>&bull;</span>
                    <span>Opened {{ $ticket->created_at->format('M d, Y H:i') }}</span>
                    @if($ticket->domain)
                        <span>&bull;</span>
                        <span>Domain: <strong class="text-brand-600 dark:text-[#F6821F]">{{ $ticket->domain->domain_name }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($ticket->status === 'open')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/50">Open</span>
            @elseif($ticket->status === 'answered')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-300 border border-indigo-200/50">Answered</span>
            @elseif($ticket->status === 'customer_reply')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200/50">Client Reply</span>
            @elseif($ticket->status === 'closed')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">Closed</span>
            @endif

            @if($ticket->status !== 'closed')
                <form action="{{ route('client.tickets.close', $ticket->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to close this ticket?');">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-xs font-semibold shadow-sm transition">
                        Close Ticket
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Original Ticket Content Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-950/50 text-brand-700 dark:text-[#F6821F] font-bold flex items-center justify-center text-sm">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ auth()->user()->name }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">(Client)</span></h4>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="text-sm text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
            {{ $ticket->message }}
        </div>

        @if(!empty($ticket->attachments))
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-xs font-semibold uppercase text-slate-400 dark:text-slate-500 mb-2">Attachments</p>
                <div class="flex flex-wrap gap-2">
                    @foreach((array)$ticket->attachments as $att)
                        <a href="{{ Storage::url($att) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-300 transition">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Attachment
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Replies Thread -->
    @foreach($ticket->replies as $reply)
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border {{ $reply->is_staff_reply ? 'border-brand-200 dark:border-brand-900/60 ring-1 ring-brand-50 dark:ring-brand-950/20' : 'border-slate-200 dark:border-slate-800' }} p-6">
            <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full {{ $reply->is_staff_reply ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }} font-bold flex items-center justify-center text-sm shadow-sm">
                        {{ $reply->is_staff_reply ? 'S' : substr($reply->user?->name ?? 'U', 0, 2) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ $reply->is_staff_reply ? ($reply->user?->name ?? 'Support Engineer') : ($reply->user?->name ?? 'Client') }}</h4>
                            @if($reply->is_staff_reply)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 dark:bg-brand-950/50 text-brand-700 dark:text-[#F6821F] uppercase tracking-wide">Staff</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wide">Client</span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $reply->created_at->format('M d, Y h:i A') }} ({{ $reply->created_at->diffForHumans() }})</span>
                    </div>
                </div>
            </div>

            <div class="text-sm text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                {{ $reply->message }}
            </div>

            @if(!empty($reply->attachments))
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-semibold uppercase text-slate-400 dark:text-slate-500 mb-2">Attachments</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach((array)$reply->attachments as $att)
                            <a href="{{ Storage::url($att) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-300 transition">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                File
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    <!-- Reply Box -->
    @if($ticket->status !== 'closed')
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Post a Reply</h3>
            <form action="{{ route('client.tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <textarea name="message" rows="5" required placeholder="Type your response here..."
                        class="w-full px-4 py-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F] shadow-sm transition">{{ old('message') }}</textarea>
                    @error('message') <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Add Attachments</label>
                    <input type="file" name="attachments[]" multiple
                        class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-700 cursor-pointer">
                    @error('attachments') <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-md transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 text-center text-sm text-slate-600 dark:text-slate-300">
            This ticket is closed. To open a new inquiry, please <a href="{{ route('client.tickets.create') }}" class="text-brand-600 dark:text-[#F6821F] font-bold hover:underline">create a new support ticket</a>.
        </div>
    @endif
</div>
@endsection
