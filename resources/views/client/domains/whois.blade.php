@extends('layouts.app', ['title' => 'WHOIS Contacts - ' . $domain->domain_name, 'header' => 'WHOIS Contact Details'])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Domain Sub-navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto border-b border-slate-200 pb-2 text-xs font-bold uppercase tracking-wider text-slate-600">
        <a href="{{ route('client.domains.show', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Overview</a>
        <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Nameservers</a>
        <a href="{{ route('client.domains.dns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">DNS Zone</a>
        <a href="{{ route('client.domains.child-ns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Child Nameservers</a>
        <a href="{{ route('client.domains.whois.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg bg-indigo-50 text-indigo-600 whitespace-nowrap">WHOIS Contacts</a>
        <a href="{{ route('client.domains.email-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Email Forwards</a>
        <a href="{{ route('client.domains.url-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">URL Redirects</a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900 mb-1">Registrant Contact for {{ $domain->domain_name }}</h2>
        <p class="text-xs text-slate-500 mb-6">
            Update the official legal owner contact details registered with ICANN and the upstream domain registry.
        </p>

        @php
            $contact = $domain->registrantContact ?? $domain->user;
        @endphp

        <form action="{{ route('client.domains.whois.update', $domain->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $contact->first_name ?? explode(' ', $contact->name ?? '')[0]) }}" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $contact->last_name ?? (explode(' ', $contact->name ?? '')[1] ?? 'Owner')) }}" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $contact->email) }}" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $contact->phone_number ?? $contact->phone) }}" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Organization / Company</label>
                <input type="text" name="company_name" value="{{ old('company_name', $contact->company_name) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Address</label>
                <input type="text" name="address_line_1" value="{{ old('address_line_1', $contact->address_line_1 ?? $contact->address_1) }}" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $contact->city) }}" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state', $contact->state) }}" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $contact->postal_code) }}" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Country (ISO 2)</label>
                    <input type="text" name="country" value="{{ old('country', $contact->country) }}" maxlength="2" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm uppercase focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow">
                    Update Registry WHOIS
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
