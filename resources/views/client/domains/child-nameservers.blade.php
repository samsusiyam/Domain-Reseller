@extends('layouts.app', ['title' => 'Child Nameservers - ' . $domain->domain_name, 'header' => 'Child Nameservers (Glue Records)'])

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ addModal: false }">
    <!-- Domain Sub-navigation Tabs -->
    <x-domain-tabs :domain="$domain" active="child-ns" />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Registered Child Nameservers</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Register authoritative glue records at the root registry (e.g. ns1.{{ $domain->domain_name }} pointing to an IP address).</p>
        </div>
        <button type="button" @click="addModal = true" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Register Child Host</span>
        </button>
    </div>

    <!-- Child Host Records Table -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium font-sans">
                    <tr>
                        <th class="px-5 py-3">Host / Subdomain</th>
                        <th class="px-5 py-3">IPv4 / IPv6 Address</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs text-slate-700 dark:text-slate-300">
                    @forelse($domain->childNameservers as $child)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3 font-bold text-[#F6821F]">{{ $child->hostname }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">{{ $child->ip_address }}</td>
                            <td class="px-5 py-3 text-right font-sans">
                                <form action="{{ route('client.domains.child-ns.destroy', [$domain->id, $child->id]) }}" method="POST" onsubmit="return confirm('Delete this child nameserver at the registry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-slate-400 font-sans text-xs">
                                No child nameservers registered for this domain. Click "Register Child Host" to create your first glue record.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="addModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Register Child Host (Glue Record)</h3>
                    <button type="button" @click="addModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('client.domains.child-ns.store', $domain->id) }}" method="POST" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Host Prefix</label>
                        <div class="flex items-center">
                            <input type="text" name="hostname" placeholder="ns1" required class="cf-input h-9 text-xs font-mono rounded-r-none border-r-0">
                            <span class="h-9 px-3 flex items-center bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-r-lg text-xs text-slate-500 font-mono">.{{ $domain->domain_name }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Target IP Address</label>
                        <input type="text" name="ip_address" placeholder="192.0.2.1" required class="cf-input h-9 text-xs font-mono">
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button type="button" @click="addModal = false" class="cf-btn cf-btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="cf-btn cf-btn-primary text-xs font-semibold cursor-pointer">Register Host</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
