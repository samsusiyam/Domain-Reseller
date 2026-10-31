@extends('layouts.app', ['title' => 'WHOIS Contacts - ' . $domain->domain_name, 'header' => 'WHOIS Contact Details'])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Domain Sub-navigation Tabs -->
    <x-domain-tabs :domain="$domain" active="whois" />

    <div class="bg-white dark:bg-[#111827] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight mb-1">Registrant Contact for {{ $domain->domain_name }}</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
            Official legal registrant details on file with ICANN and upstream registry. Updating here syncs directly to the registrar.
        </p>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs rounded-lg border border-green-200 dark:border-green-800">
                {{ session('success') }}
            </div>
        @endif

        @php
            $liveReg = $liveWhois?->registrant ?? [];
            $contact = $domain->registrantContact;
            $firstName = old('first_name', $contact->first_name ?? $liveReg['first_name'] ?? explode(' ', $domain->user->name ?? '')[0]);
            $lastName = old('last_name', $contact->last_name ?? $liveReg['last_name'] ?? (explode(' ', $domain->user->name ?? '')[1] ?? 'Owner'));
            $emailVal = old('email', $contact->email ?? $liveReg['email'] ?? $domain->user->email ?? '');
            $phoneVal = old('phone_number', $contact->phone_number ?? $contact->phone ?? $liveReg['phone_number'] ?? $domain->user->phone ?? '');
            $companyVal = old('company_name', $contact->company_name ?? $liveReg['company_name'] ?? '');
            $addressVal = old('address_line_1', $contact->address_line_1 ?? $contact->address_1 ?? $liveReg['address_line_1'] ?? '');
            $cityVal = old('city', $contact->city ?? $liveReg['city'] ?? 'Dinajpur');
            $stateVal = old('state', $contact->state ?? $liveReg['state'] ?? 'Dinajpur');
            $zipVal = old('postal_code', $contact->postal_code ?? $liveReg['postal_code'] ?? '5200');
            $countryVal = old('country', $contact->country ?? $liveReg['country'] ?? 'BD');
        @endphp

        <form action="{{ route('client.domains.whois.update', $domain->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">First Name</label>
                    <input type="text" name="first_name" value="{{ $firstName }}" required class="cf-input h-9 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ $lastName }}" required class="cf-input h-9 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ $emailVal }}" required class="cf-input h-9 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ $phoneVal }}" required class="cf-input h-9 text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Company / Organization (Optional)</label>
                <input type="text" name="company_name" value="{{ $companyVal }}" class="cf-input h-9 text-xs">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Address Line 1</label>
                <input type="text" name="address_line_1" value="{{ $addressVal }}" required class="cf-input h-9 text-xs">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">City</label>
                    <input type="text" name="city" value="{{ $cityVal }}" required class="cf-input h-9 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">State / Region</label>
                    <input type="text" name="state" value="{{ $stateVal }}" required class="cf-input h-9 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ $zipVal }}" required class="cf-input h-9 text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Country (ISO 2-letter code, e.g. BD, US)</label>
                <input type="text" name="country" value="{{ $countryVal }}" maxlength="2" required class="cf-input h-9 text-xs uppercase font-mono w-28">
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <button type="submit" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer">
                    Save WHOIS Details to Registry
                </button>
                <span class="text-[11px] text-slate-400">Pushes directly to upstream registrar</span>
            </div>
        </form>
    </div>
</div>
@endsection
