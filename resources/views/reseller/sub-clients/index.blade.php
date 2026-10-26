@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">End-Customer Contacts</h1>
            <p class="text-sm text-slate-500 mt-1">Directory of WHOIS Registrant, Technical, and Billing contact profiles created via your WHMCS or API orders.</p>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($contacts->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700">No Contacts Found</h3>
                <p class="text-sm text-slate-500 mt-1">When your clients place domain orders in WHMCS, their registrant contact profiles will be listed here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Contact Name</th>
                            <th class="px-6 py-3.5">Company / Org</th>
                            <th class="px-6 py-3.5">Email Address</th>
                            <th class="px-6 py-3.5">Phone</th>
                            <th class="px-6 py-3.5">Country</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($contacts as $c)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    <a href="{{ route('reseller.sub-clients.show', $c->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                        {{ $c->first_name }} {{ $c->last_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ $c->company_name ?: '—' }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600">
                                    {{ $c->email }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-xs">
                                    {{ $c->phone }}
                                </td>
                                <td class="px-6 py-4 text-slate-700 font-semibold text-xs uppercase">
                                    {{ $c->country }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('reseller.sub-clients.show', $c->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                        View Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($contacts->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $contacts->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
