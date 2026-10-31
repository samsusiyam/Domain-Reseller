@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Event Webhooks</h1>
            <p class="text-sm text-slate-500 mt-1">Receive real-time HTTP POST notifications whenever domains are registered, renewed, or transfer status changes.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Webhook Form -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-1">Add Webhook Endpoint</h3>
                <p class="text-xs text-slate-500 mb-5">Your server will receive JSON payloads with HMAC-SHA256 signatures.</p>

                <form action="{{ route('reseller.webhooks.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Payload URL</label>
                        <input type="url" name="target_url" placeholder="https://yourdomain.com/webhooks/listener" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F]"
                            value="{{ old('target_url') }}">
                        @error('target_url') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Subscribe to Events</label>
                        <div class="space-y-2 max-h-56 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 text-xs">
                            @foreach($availableEvents as $eventKey => $eventLabel)
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="subscribed_events[]" value="{{ $eventKey }}"
                                        class="mt-0.5 rounded border-slate-300 text-[#F6821F] focus:ring-[#F6821F]" checked>
                                    <div>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $eventLabel }}</span>
                                        <span class="font-mono text-[10px] text-slate-500">{{ $eventKey }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('subscribed_events') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 cf-btn cf-btn-primary text-white font-medium rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Register Webhook
                    </button>
                </form>
            </div>
        </div>

        <!-- Registered Webhooks List -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Active Webhook Listeners</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Payloads are retried up to 3 times on connection failures.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full">
                        {{ $webhooks->count() }} Endpoints
                    </span>
                </div>

                @if($webhooks->isEmpty())
                    <div class="text-center py-16 px-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h4 class="text-base font-semibold text-slate-700">No Webhooks Configured</h4>
                        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Register your billing platform's listener URL to receive real-time updates.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($webhooks as $hook)
                            <div class="p-6 hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                    <div class="space-y-2 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-sm text-slate-900 dark:text-white break-all">{{ $hook->target_url }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $hook->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $hook->status }}
                                            </span>
                                        </div>
                                        <div class="text-xs font-mono text-slate-500">
                                            <span class="text-slate-400 font-sans font-bold text-[10px] mr-1 uppercase">Signing Secret:</span>
                                            <span class="bg-slate-100 px-2 py-0.5 rounded select-all">{{ $hook->secret }}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            @foreach((array)$hook->subscribed_events as $ev)
                                                <span class="px-2 py-0.5 bg-orange-50 dark:bg-orange-950/30 text-[#F6821F] font-mono text-[10px] rounded-md font-semibold">
                                                    {{ $ev }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-start">
                                        <!-- Test Webhook -->
                                        <form action="{{ route('reseller.webhooks.test', $hook->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 border border-orange-200 dark:border-orange-900/40 text-[#F6821F] hover:bg-orange-50 dark:bg-orange-950/30 text-xs font-semibold rounded-lg transition">
                                                Test Ping
                                            </button>
                                        </form>

                                        <!-- Delete Webhook -->
                                        <form action="{{ route('reseller.webhooks.destroy', $hook->id) }}" method="POST" onsubmit="return confirm('Remove this webhook listener?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold rounded-lg transition">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
