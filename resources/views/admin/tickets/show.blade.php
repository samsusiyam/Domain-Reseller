@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tickets.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-slate-500">#{{ substr($ticket->uuid ?? (string)$ticket->id, 0, 8) }}</span>
                    <h1 class="text-xl font-bold text-slate-900">{{ $ticket->subject }}</h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Customer: <a href="{{ route('admin.clients.show', $ticket->user_id) }}" class="text-indigo-600 font-bold hover:underline">{{ $ticket->user?->name ?? 'Guest' }}</a>
                    &bull; Department: <strong class="capitalize">{{ $ticket->department }}</strong>
                    &bull; Opened {{ $ticket->created_at->format('M d, Y H:i') }}
                </p>
            </div>
        </div>

        <!-- Quick Status Update Form -->
        <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST" class="flex items-center gap-2">
            @csrf
            <select name="status" class="px-3 py-1.5 text-xs font-semibold border border-slate-300 rounded-lg">
                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                <option value="customer_reply" {{ $ticket->status === 'customer_reply' ? 'selected' : '' }}>Client Reply</option>
                <option value="answered" {{ $ticket->status === 'answered' ? 'selected' : '' }}>Answered</option>
                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold transition hover:bg-slate-800">
                Update
            </button>
        </form>
    </div>

    <!-- Original Message -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-3 border-b border-slate-100 pb-3 mb-4">
            <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs">
                {{ substr($ticket->user?->name ?? 'U', 0, 2) }}
            </div>
            <div>
                <h4 class="font-bold text-slate-900 text-sm">{{ $ticket->user?->name ?? 'Customer' }} <span class="text-xs font-normal text-slate-400">({{ $ticket->user?->email }})</span></h4>
                <span class="text-xs text-slate-400">{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="text-sm text-slate-800 whitespace-pre-line leading-relaxed">
            {{ $ticket->message }}
        </div>
    </div>

    <!-- Replies Thread -->
    @foreach($ticket->replies as $reply)
        <div class="bg-white rounded-2xl shadow-sm border {{ $reply->is_staff_reply ? 'border-indigo-200 ring-1 ring-indigo-50' : 'border-slate-200' }} p-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3 mb-4">
                <div class="w-9 h-9 rounded-full {{ $reply->is_staff_reply ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700' }} font-bold flex items-center justify-center text-xs">
                    {{ $reply->is_staff_reply ? 'ADM' : substr($reply->user?->name ?? 'U', 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-slate-900 text-sm">{{ $reply->user?->name ?? ($reply->is_staff_reply ? 'Staff Admin' : 'Client') }}</h4>
                        @if($reply->is_staff_reply)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase">Support Staff</span>
                        @endif
                    </div>
                    <span class="text-xs text-slate-400">{{ $reply->created_at->format('M d, Y h:i A') }} ({{ $reply->created_at->diffForHumans() }})</span>
                </div>
            </div>

            <div class="text-sm text-slate-800 whitespace-pre-line leading-relaxed">
                {{ $reply->message }}
            </div>
        </div>
    @endforeach

    <!-- Staff Reply Box -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <h3 class="text-base font-bold text-slate-900 mb-4">Post Staff Response</h3>
        <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <textarea name="message" rows="5" required placeholder="Type official response to customer..."
                    class="w-full px-4 py-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">{{ old('message') }}</textarea>
                @error('message') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-600">Set Status After Reply:</label>
                    <select name="status" class="px-3 py-1.5 text-xs font-semibold border border-slate-300 rounded-lg">
                        <option value="answered" selected>Answered</option>
                        <option value="in_progress">In Progress</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Submit Response
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
