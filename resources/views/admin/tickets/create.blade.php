@extends('layouts.admin', ['title' => 'Open Support Ticket', 'header' => 'New Ticket'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tickets.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Open New Support Ticket</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Initiate a support thread directly to a client or reseller account</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.tickets.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-6 shadow-xs">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3">
                    <label for="user_id" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Client / Recipient <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" id="user_id" required class="cf-input w-full">
                        <option value="">-- Choose Client Account --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                #{{ $user->id }} — {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="department" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select name="department" id="department" required class="cf-input w-full">
                        <option value="technical" {{ old('department') == 'technical' ? 'selected' : '' }}>Technical Support</option>
                        <option value="billing" {{ old('department') == 'billing' ? 'selected' : '' }}>Billing & Invoices</option>
                        <option value="domains" {{ old('department') == 'domains' ? 'selected' : '' }}>Domain Transfers & DNS</option>
                        <option value="general" {{ old('department') == 'general' ? 'selected' : '' }}>General Inquiries</option>
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Priority Level <span class="text-red-500">*</span>
                    </label>
                    <select name="priority" id="priority" required class="cf-input w-full">
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label for="subject" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" id="subject" required
                           value="{{ old('subject') }}"
                           placeholder="e.g. Action Required: Verification of WHOIS contact details"
                           class="cf-input w-full text-sm">
                </div>

                <div class="md:col-span-3">
                    <label for="message" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Message Content <span class="text-red-500">*</span>
                    </label>
                    <textarea name="message" id="message" rows="6" required
                              placeholder="Write your message or notice to the client here..."
                              class="cf-input w-full font-sans text-sm">{{ old('message') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.tickets.index') }}" class="cf-btn cf-btn-secondary text-xs">Cancel</a>
            <button type="submit" class="cf-btn cf-btn-primary text-xs inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                Send & Open Ticket
            </button>
        </div>
    </form>
</div>
@endsection
