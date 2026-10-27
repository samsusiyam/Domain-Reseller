@extends('layouts.app', ['title' => 'WHOIS Contacts - ' . $domain->domain_name, 'header' => 'WHOIS Contact Details'])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Domain Sub-navigation Tabs -->
    <x-domain-tabs :domain="$domain" active="whois" />

    <div class="bg-white dark:bg-[#111827] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight mb-1">Registrant Contact for {{ $domain->domain_name }}</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
            Official legal registrant details on file with ICANN and upstream registry. Free WHOIS privacy is active by default.
        </p>

        @php
            $contact = $domain->registrantContact ?? $domain->user;
        @endphp

        <form action="{{ route('client.domains.whois.update', $domain->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $contact->first_name ?? explode(' ', $contact->name ?? '')[0]) }}" required class="cf-input h-9 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $contact->last_name ?? (explode(' ', $contact->name ?? '')[1] ?? 'Owner')) }}" required class="cf-input h-9 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $contact->email) }}" required class="cf-input h-9 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $contact->phone_number ?? $contact->phone) }}" required class="cf-input h-9 text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Company / Organization (Optional)</label>
                <input type="text" name="company_name" value="{{ old('company_name', $contact->company_name) }}" class="cf-input h-9 text-xs">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Address Line 1</label>
                <input type="text" name="address_line_1" value="{{ old('address_line_1', $contact->address_line_1 ?? $contact->address_1) }}" required class="cf-input h-9 text-xs">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $contact->city) }}" required class="cf-input h-9 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">State / Region</label>
                    <input type="text" name="state" value="{{ old('state', $contact->state) }}" required class="cf-input h-9 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $contact->postal_code) }}" required class="cf-input h-9 text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Country (ISO 2-letter code, e.g. BD, US)</label>
                <input type="text" name="country" value="{{ old('country', $contact->country) }}" maxlength="2" required class="cf-input h-9 text-xs uppercase font-mono w-28">
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer">
                    Save WHOIS Details
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
