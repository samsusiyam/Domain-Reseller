@extends('layouts.reseller')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('reseller.sub-clients.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $contact->first_name }} {{ $contact->last_name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Contact Card ID: #{{ $contact->id }} &bull; Created {{ $contact->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    <!-- Contact Details Card -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 pb-3 mb-6">Contact Profile Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Company / Organization</span>
                <p class="font-medium text-slate-800 dark:text-slate-200">{{ $contact->company_name ?: 'None / Individual' }}</p>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Email Address</span>
                <p class="font-mono text-slate-800 dark:text-slate-200">{{ $contact->email }}</p>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Phone Number</span>
                <p class="font-mono text-slate-800 dark:text-slate-200">{{ $contact->phone }}</p>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Street Address</span>
                <p class="text-slate-800 dark:text-slate-200">{{ $contact->address_line1 }} {{ $contact->address_line2 }}</p>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">City, State & Postcode</span>
                <p class="text-slate-800 dark:text-slate-200">{{ $contact->city }}, {{ $contact->state }} {{ $contact->postal_code }}</p>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Country</span>
                <p class="font-bold text-slate-800 dark:text-slate-200 uppercase">{{ $contact->country }}</p>
            </div>
        </div>
    </div>

    <!-- Associated Domains Table -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Associated Domains</h3>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full">
                {{ $domains->count() }} Domains
            </span>
        </div>

        @if($domains->isEmpty())
            <div class="text-center py-12 px-4">
                <p class="text-sm text-slate-500">No domains are actively linked to this contact profile.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Domain Name</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5">Registration Date</th>
                            <th class="px-6 py-3.5">Expiry Date</th>
                            <th class="px-6 py-3.5 text-center">Auto-Renew</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($domains as $dom)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white">{{ $dom->domain_name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $dom->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($dom->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $dom->registered_at ? $dom->registered_at->format('M d, Y') : '—' }}</td>
                                <td class="px-6 py-4 text-slate-600 font-semibold">{{ $dom->expires_at ? $dom->expires_at->format('M d, Y') : '—' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($dom->auto_renew)
                                        <span class="text-emerald-600 font-bold text-xs">Enabled</span>
                                    @else
                                        <span class="text-slate-400 text-xs">Disabled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('client.domains.show', $dom->id) }}" class="text-[#F6821F] hover:text-[#E07116] font-semibold text-xs">
                                        Manage &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
