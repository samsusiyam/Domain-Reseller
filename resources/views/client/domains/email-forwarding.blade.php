@extends('layouts.app')

@section('title', 'Email Forwarding - ' . $domain->domain_name)

@section('content')
<div class="space-y-6">
    <!-- Domain Header & Sub-Nav -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('client.domains.show', $domain->id) }}" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <h1 class="text-2xl font-bold text-slate-800">{{ $domain->domain_name }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ $domain->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ ucfirst($domain->status) }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1">Manage email forwards and routing aliases for this domain.</p>
            </div>
            <div>
                <a href="{{ route('client.domains.show', $domain->id) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition">
                    Domain Overview
                </a>
            </div>
        </div>

        <!-- Domain Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 mt-6 -mb-6 overflow-x-auto text-sm">
            <a href="{{ route('client.domains.show', $domain->id) }}" class="px-4 py-3 text-slate-600 hover:text-slate-900 border-b-2 border-transparent whitespace-nowrap font-medium">Overview</a>
            <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="px-4 py-3 text-slate-600 hover:text-slate-900 border-b-2 border-transparent whitespace-nowrap font-medium">Nameservers</a>
            <a href="{{ route('client.domains.dns.index', $domain->id) }}" class="px-4 py-3 text-slate-600 hover:text-slate-900 border-b-2 border-transparent whitespace-nowrap font-medium">DNS Records</a>
            <a href="{{ route('client.domains.child-ns.index', $domain->id) }}" class="px-4 py-3 text-slate-600 hover:text-slate-900 border-b-2 border-transparent whitespace-nowrap font-medium">Glue Records</a>
            <a href="{{ route('client.domains.whois.edit', $domain->id) }}" class="px-4 py-3 text-slate-600 hover:text-slate-900 border-b-2 border-transparent whitespace-nowrap font-medium">WHOIS Contacts</a>
            <a href="{{ route('client.domains.email-forwarding.index', $domain->id) }}" class="px-4 py-3 text-brand-600 border-b-2 border-brand-600 font-semibold whitespace-nowrap">Email Forwarding</a>
            <a href="{{ route('client.domains.url-forwarding.index', $domain->id) }}" class="px-4 py-3 text-slate-600 hover:text-slate-900 border-b-2 border-transparent whitespace-nowrap font-medium">URL Redirection</a>
        </div>
    </div>

    <!-- Notice -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="text-sm text-blue-800">
            <span class="font-semibold">Notice:</span> For email forwarding to function properly, your domain's MX records must point to our default email routing cluster. When you add your first email forwarder, required DNS entries will be verified.
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Forwarder Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Create New Forwarder</h3>
                <form action="{{ route('client.domains.email-forwarding.store', $domain->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Incoming Address</label>
                        <div class="flex rounded-lg shadow-sm border border-slate-300 overflow-hidden focus-within:ring-2 focus-within:ring-brand-500 focus-within:border-brand-500">
                            <input type="text" name="source_prefix" placeholder="sales" required
                                class="flex-1 px-3 py-2 text-sm text-slate-800 border-none focus:outline-none"
                                value="{{ old('source_prefix') }}">
                            <span class="inline-flex items-center px-3 bg-slate-50 text-slate-500 text-sm border-l border-slate-300">
                                @ {{ $domain->domain_name }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">E.g., "info", "support", or "*" for catch-all.</p>
                        @error('source_prefix') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Forward To (Destination)</label>
                        <input type="email" name="destination_email" placeholder="you@gmail.com" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            value="{{ old('destination_email') }}">
                        <p class="text-xs text-slate-500 mt-1">Where all incoming messages should be delivered.</p>
                        @error('destination_email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Email Forwarder
                    </button>
                </form>
            </div>
        </div>

        <!-- Existing Forwarders Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Active Email Forwarders</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Incoming emails to these addresses are automatically routed.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full">
                        {{ $forwarders->count() }} Configured
                    </span>
                </div>

                @if($forwarders->isEmpty())
                    <div class="text-center py-12 px-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="text-base font-semibold text-slate-700">No Email Forwarders Set</h4>
                        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Create your first forwarder using the form on the left to route custom domain emails to your personal inbox.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3.5">Alias Address</th>
                                    <th class="px-6 py-3.5"></th>
                                    <th class="px-6 py-3.5">Destination</th>
                                    <th class="px-6 py-3.5 text-center">Status</th>
                                    <th class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($forwarders as $forward)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-6 py-4 font-mono font-medium text-slate-800">
                                            {{ $forward->source_prefix }}@<span class="text-slate-500">{{ $domain->domain_name }}</span>
                                        </td>
                                        <td class="px-2 py-4 text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-slate-700">
                                            {{ $forward->destination_email }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                {{ $forward->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $forward->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('client.domains.email-forwarding.destroy', [$domain->id, $forward->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this forwarder?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-semibold inline-flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
