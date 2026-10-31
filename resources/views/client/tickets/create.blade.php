@extends('layouts.app')

@section('title', 'Open Support Ticket')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('client.tickets.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Open Support Ticket</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Submit your query to our technical operations or billing team.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <form action="{{ route('client.tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Subject -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Subject</label>
                <input type="text" name="subject" required placeholder="Brief summary of your inquiry"
                    class="w-full px-4 py-2.5 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F] shadow-sm transition"
                    value="{{ old('subject') }}">
                @error('subject') <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Department -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Department</label>
                    <select name="department" required class="w-full px-3 py-2.5 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F] shadow-sm transition">
                        <option value="technical" {{ old('department') === 'technical' ? 'selected' : '' }}>Technical Support</option>
                        <option value="domain" {{ old('department') === 'domain' ? 'selected' : '' }}>Domain Registration</option>
                        <option value="billing" {{ old('department') === 'billing' ? 'selected' : '' }}>Billing & Accounts</option>
                        <option value="general" {{ old('department') === 'general' ? 'selected' : '' }}>General Questions</option>
                    </select>
                    @error('department') <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                    <select name="priority" required class="w-full px-3 py-2.5 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F] shadow-sm transition">
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority') <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Related Domain -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Related Domain (Optional)</label>
                    <select name="domain_id" class="w-full px-3 py-2.5 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F] shadow-sm transition">
                        <option value="">None / Not Applicable</option>
                        @foreach($domains as $dom)
                            <option value="{{ $dom->id }}" {{ old('domain_id') == $dom->id ? 'selected' : '' }}>{{ $dom->domain_name }}</option>
                        @endforeach
                    </select>
                    @error('domain_id') <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Message -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Message Description</label>
                <textarea name="message" rows="6" required placeholder="Please provide specific details, error logs, or transaction references so we can resolve this quickly."
                    class="w-full px-4 py-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F] shadow-sm transition">{{ old('message') }}</textarea>
                @error('message') <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Attachments -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Attachments (Screenshots / Logs)</label>
                <input type="file" name="attachments[]" multiple
                    class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-700 cursor-pointer">
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1.5">Supported formats: JPG, PNG, PDF, TXT, ZIP. Max file size: 5MB per file.</p>
                @error('attachments') <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('client.tickets.index') }}" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
