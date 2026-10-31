@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ testLoading: false, testMsg: '', testSuccess: false }">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Mail Server & SMTP Configuration</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Setup outbound SMTP credentials for customer notifications, receipts, and alert emails.</p>
    </div>

    <x-admin-settings-tabs active="email" />

    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <form action="{{ route('admin.settings.email.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Mail Driver / Mailer</label>
                    <select name="mail_mailer" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none font-semibold">
                        <option value="smtp" {{ old('mail_mailer', $settings['mail_mailer']) === 'smtp' ? 'selected' : '' }}>SMTP (Recommended)</option>
                        <option value="sendmail" {{ old('mail_mailer', $settings['mail_mailer']) === 'sendmail' ? 'selected' : '' }}>Sendmail (cPanel Native)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">SMTP Host</label>
                    <input type="text" name="mail_host" required class="w-full px-3 py-2 font-mono text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none"
                        value="{{ old('mail_host', $settings['mail_host']) }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">SMTP Port</label>
                    <input type="text" name="mail_port" required class="w-full px-3 py-2 font-mono text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none"
                        value="{{ old('mail_port', $settings['mail_port']) }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Encryption Protocol</label>
                    <select name="mail_encryption" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none font-semibold">
                        <option value="tls" {{ old('mail_encryption', $settings['mail_encryption']) === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                        <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption']) === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                        <option value="none" {{ old('mail_encryption', $settings['mail_encryption']) === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">SMTP Username</label>
                    <input type="text" name="mail_username" class="w-full px-3 py-2 font-mono text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none"
                        value="{{ old('mail_username', $settings['mail_username']) }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">SMTP Password</label>
                    <input type="password" name="mail_password" placeholder="••••••••••••" class="w-full px-3 py-2 font-mono text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">From Sender Email</label>
                    <input type="email" name="mail_from_address" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none"
                        value="{{ old('mail_from_address', $settings['mail_from_address']) }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">From Sender Name</label>
                    <input type="text" name="mail_from_name" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none"
                        value="{{ old('mail_from_name', $settings['mail_from_name']) }}">
                </div>
            </div>

            <!-- Test Connection Alert -->
            <div x-show="testMsg" x-cloak class="p-4 rounded-xl text-xs font-bold"
                :class="testSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                <p x-text="testMsg"></p>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="
                    testLoading = true;
                    testMsg = '';
                    fetch('{{ route('admin.settings.email.test') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        testLoading = false;
                        testSuccess = data.success;
                        testMsg = data.message;
                    })
                    .catch(err => {
                        testLoading = false;
                        testSuccess = false;
                        testMsg = 'Connection error: ' + err;
                    });
                " :disabled="testLoading" class="px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white hover:bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <span x-show="!testLoading">✉ Send Test Email</span>
                    <span x-show="testLoading" x-cloak>Sending...</span>
                </button>

                <button type="submit" class="px-6 py-2.5 bg-[#F6821F] hover:bg-[#E07116] text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Save Email Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
