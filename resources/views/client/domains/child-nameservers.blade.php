@extends('layouts.app', ['title' => 'Child Nameservers - ' . $domain->domain_name, 'header' => 'Child Nameservers (Glue Records)'])

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ addModal: false }">
    <!-- Domain Sub-navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto border-b border-slate-200 pb-2 text-xs font-bold uppercase tracking-wider text-slate-600">
        <a href="{{ route('client.domains.show', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Overview</a>
        <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Nameservers</a>
        <a href="{{ route('client.domains.dns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">DNS Zone</a>
        <a href="{{ route('client.domains.child-ns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg bg-indigo-50 text-indigo-600 whitespace-nowrap">Child Nameservers</a>
        <a href="{{ route('client.domains.whois.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">WHOIS Contacts</a>
        <a href="{{ route('client.domains.email-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Email Forwards</a>
        <a href="{{ route('client.domains.url-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">URL Redirects</a>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Registered Child Nameservers</h2>
            <p class="text-xs text-slate-500">Create custom glue records (e.g. ns1.{{ $domain->domain_name }} pointing to an IP address).</p>
        </div>
        <button type="button" @click="addModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow">
            + Register Child Host
        </button>
    </div>

    <!-- Child Host Records Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm font-mono">
            <thead class="bg-slate-50 text-slate-600 font-bold text-xs uppercase tracking-wider font-sans">
                <tr>
                    <th class="py-3 px-6">Host / Subdomain</th>
                    <th class="py-3 px-6">IPv4 / IPv6 Address</th>
                    <th class="py-3 px-6 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-xs text-slate-800">
                @forelse($domain->childNameservers as $child)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-6 font-bold text-indigo-600">{{ $child->hostname }}</td>
                        <td class="py-3.5 px-6 font-semibold">{{ $child->ip_address }}</td>
                        <td class="py-3.5 px-6 text-right font-sans">
                            <form action="{{ route('client.domains.child-ns.destroy', [$domain->id, $child->id]) }}" method="POST" onsubmit="return confirm('Delete this child nameserver at the registry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-12 text-center text-slate-400 font-sans text-xs">
                            No child nameservers registered for this domain.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="addModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-4">Register Child Host (Glue Record)</h3>

            <form action="{{ route('client.domains.child-ns.store', $domain->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Host Name</label>
                    <div class="flex items-center">
                        <input type="text" name="hostname" placeholder="ns1" required class="flex-1 px-4 py-2 border border-slate-300 rounded-l-xl text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <span class="px-3 py-2 bg-slate-100 border border-l-0 border-slate-300 rounded-r-xl text-xs text-slate-500 font-mono">.{{ $domain->domain_name }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">IP Address</label>
                    <input type="text" name="ip_address" placeholder="192.0.2.1" required class="w-full px-4 py-2 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="pt-4 flex items-center justify-end gap-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow">Register Host</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
