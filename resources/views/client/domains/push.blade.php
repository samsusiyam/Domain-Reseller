@extends('layouts.app', ['title' => 'Push ' . $domain->domain_name, 'header' => 'Internal Domain Handover'])

@section('content')
<div class="max-w-3xl space-y-6">

    <!-- Top Domain Banner -->
    <div class="bg-white dark:bg-[#111827] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-orange-50 text-[#F6821F] dark:bg-orange-950/40 border border-orange-200 dark:border-orange-900/40">
                    Free & Instant
                </span>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white font-mono">{{ $domain->domain_name }}</h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Move domain between HostNibo clients or resellers without EPP code or transfer fees.</p>
        </div>

        <a href="{{ route('client.domains.show', $domain->id) }}" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-semibold self-start sm:self-auto">
            &larr; Back to Domain
        </a>
    </div>

    <x-domain-tabs :domain="$domain" active="push" />

    <!-- Guidelines & Protection Notice -->
    <div class="p-4 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 text-xs text-amber-900 dark:text-amber-300 space-y-2">
        <div class="flex items-center gap-2 font-bold text-sm">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>How Internal Domain Push Works</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-slate-700 dark:text-slate-300 ml-1">
            <li><strong>Zero Cost:</strong> Internal pushes are completely free. No registry transfer fee or renewal extension is charged.</li>
            <li><strong>Instantaneous:</strong> The domain immediately leaves your account and appears in the recipient's dashboard.</li>
            <li><strong>Prerequisites:</strong> The recipient must already have an active account on HostNibo. The domain must be unlocked.</li>
            <li><strong>Irreversible:</strong> Once pushed, full administrative control is ceded to the recipient.</li>
        </ul>
    </div>

    <!-- Push Form Card -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('client.domains.push.process', $domain->id) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Domain Status Check -->
            @if($domain->is_locked)
                <div class="p-4 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/40 text-xs text-rose-800 dark:text-rose-300 flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong class="block font-bold">Domain is currently Locked</strong>
                        <span>Registrar lock is active. Please return to <a href="{{ route('client.domains.show', $domain->id) }}" class="underline font-semibold hover:text-rose-900 dark:hover:text-rose-200">Domain Overview</a> and toggle off the transfer lock before pushing.</span>
                    </div>
                </div>
            @endif

            <!-- Recipient Email -->
            <div>
                <label for="recipient_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Recipient Account Email Address *
                </label>
                <div class="relative">
                    <input type="email"
                           id="recipient_email"
                           name="recipient_email"
                           value="{{ old('recipient_email') }}"
                           required
                           placeholder="partner@example.com"
                           class="w-full px-3.5 py-2.5 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F]">
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Must match the registered email address of an active HostNibo client or reseller account.</p>
                @error('recipient_email')
                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirmation Checkbox -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox"
                           name="confirm_transfer"
                           value="1"
                           required
                           class="mt-0.5 rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F]">
                    <span class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                        I confirm that I wish to transfer ownership of <strong>{{ $domain->domain_name }}</strong> to the specified user immediately. I understand this action cannot be undone.
                    </span>
                </label>
                @error('confirm_transfer')
                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('client.domains.show', $domain->id) }}" class="cf-btn cf-btn-secondary text-xs">
                    Cancel
                </a>
                <button type="submit"
                        @if($domain->is_locked) disabled @endif
                        class="cf-btn cf-btn-primary text-xs font-semibold px-5 flex items-center gap-1.5 shadow-sm @if($domain->is_locked) opacity-50 cursor-not-allowed @endif">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Push Domain Now (Free)</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
