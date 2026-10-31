@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Security & Access Safeguards</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Control two-factor authentication requirements, session expiry, brute-force mitigation, and API throttling.</p>
    </div>

    <x-admin-settings-tabs active="security" />

    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <form action="{{ route('admin.settings.security.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Session Lifetime (Minutes)</label>
                    <input type="number" name="session_lifetime" min="15" max="1440" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none"
                        value="{{ old('session_lifetime', $settings['session_lifetime']) }}">
                    <p class="text-[11px] text-slate-400 mt-1">Automatic logout after inactivity period.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Max Failed Login Attempts</label>
                    <input type="number" name="login_max_attempts" min="3" max="20" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none"
                        value="{{ old('login_max_attempts', $settings['login_max_attempts']) }}">
                    <p class="text-[11px] text-slate-400 mt-1">Locks account temporarily upon brute-force attempts.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">API Rate Limit (Requests / Min / IP)</label>
                    <input type="number" name="api_rate_limit" min="30" max="1000" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-2 focus:ring-[#F6821F] focus:outline-none"
                        value="{{ old('api_rate_limit', $settings['api_rate_limit']) }}">
                    <p class="text-[11px] text-slate-400 mt-1">Per-minute quota applied to Reseller API keys.</p>
                </div>

                <div class="flex items-center pt-5">
                    <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-800 dark:text-white">
                        <input type="checkbox" name="enforce_2fa" value="true" {{ old('enforce_2fa', $settings['enforce_2fa']) === 'true' ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-[#F6821F]">
                        Enforce 2FA for All Admin Accounts
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="px-6 py-2.5 bg-[#F6821F] hover:bg-[#E07116] text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Save Security Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
