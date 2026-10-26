@extends('layouts.app', ['title' => 'Manage ' . $domain->domain_name, 'header' => 'Domain Overview'])

@section('content')
<div class="space-y-6" x-data="{
    isLocked: {{ $domain->is_locked ? 'true' : 'false' }},
    isPrivate: {{ $domain->is_private ? 'true' : 'false' }},
    eppCode: '',
    loadingEpp: false,
    eppModal: false,
    toggleLock() {
        fetch('{{ route('client.domains.toggle-lock', $domain->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(res => res.json()).then(data => { this.isLocked = data.is_locked; });
    },
    togglePrivacy() {
        fetch('{{ route('client.domains.toggle-privacy', $domain->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(res => res.json()).then(data => { this.isPrivate = data.is_private; });
    },
    fetchEpp() {
        this.loadingEpp = true;
        this.eppModal = true;
        fetch('{{ route('client.domains.epp', $domain->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(res => res.json()).then(data => {
            this.loadingEpp = false;
            this.eppCode = data.epp_code || 'Unable to retrieve';
        });
    }
}">

    <!-- Top Domain Banner -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $domain->domain_name }}</h1>
                <x-badge :color="$domain->status">{{ $domain->status }}</x-badge>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                <span>Registered: <strong>{{ $domain->registration_date ? $domain->registration_date->format('M d, Y') : 'N/A' }}</strong></span>
                <span>•</span>
                <span>Expires: <strong class="{{ $domain->expires_at && $domain->expires_at->isPast() ? 'text-rose-600' : 'text-slate-900' }}">{{ $domain->expires_at ? $domain->expires_at->format('M d, Y') : 'N/A' }}</strong></span>
                @if($domain->expires_at)
                    <span>({{ $domain->expires_at->diffForHumans() }})</span>
                @endif
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Renew Form Dropdown -->
            <form action="{{ route('client.domains.renew', $domain->id) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="years" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500">
                    <option value="1">1 Year</option>
                    <option value="2">2 Years</option>
                    <option value="3">3 Years</option>
                    <option value="5">5 Years</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow">
                    Renew Now
                </button>
            </form>

            <button type="button" @click="fetchEpp()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                Get EPP Code
            </button>
        </div>
    </div>

    <!-- Domain Sub-navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto border-b border-slate-200 pb-2 text-xs font-bold uppercase tracking-wider text-slate-600">
        <a href="{{ route('client.domains.show', $domain->id) }}" class="px-3.5 py-2 rounded-lg bg-indigo-50 text-indigo-600 whitespace-nowrap">Overview</a>
        <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Nameservers</a>
        <a href="{{ route('client.domains.dns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">DNS Zone</a>
        <a href="{{ route('client.domains.child-ns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Child Nameservers</a>
        <a href="{{ route('client.domains.whois.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">WHOIS Contacts</a>
        <a href="{{ route('client.domains.email-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Email Forwards</a>
        <a href="{{ route('client.domains.url-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">URL Redirects</a>
    </div>

    <!-- Security Toggles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Registrar Lock Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Registrar Transfer Lock</h3>
                <p class="text-xs text-slate-500 mt-0.5">Prevents unauthorized domain transfers out.</p>
            </div>
            <button type="button" @click="toggleLock()"
                :class="isLocked ? 'bg-indigo-600' : 'bg-slate-200'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                <span :class="isLocked ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>

        <!-- WHOIS Privacy Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">WHOIS ID Protection</h3>
                <p class="text-xs text-slate-500 mt-0.5">Hides your personal identity from public WHOIS.</p>
            </div>
            <button type="button" @click="togglePrivacy()"
                :class="isPrivate ? 'bg-indigo-600' : 'bg-slate-200'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                <span :class="isPrivate ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>
    </div>

    <!-- Nameservers Snapshot -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Current Authoritative Nameservers</h3>
            <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Change Nameservers &rarr;</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 font-mono text-xs text-slate-800">
            @forelse($domain->nameservers ?? [] as $ns)
                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl">{{ $ns }}</div>
            @empty
                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-400">Default Nameservers</div>
            @endforelse
        </div>
    </div>

    <!-- EPP Modal -->
    <div x-show="eppModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="eppModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-2">EPP / Authorization Code</h3>
            <p class="text-xs text-slate-500 mb-4">This authorization code is required if you wish to transfer {{ $domain->domain_name }} to another registrar.</p>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-center font-mono text-base font-bold text-indigo-600 select-all mb-4">
                <span x-show="!loadingEpp" x-text="eppCode"></span>
                <span x-show="loadingEpp">Fetching from registrar...</span>
            </div>

            <button type="button" @click="eppModal = false" class="w-full py-2.5 bg-slate-900 text-white font-bold text-xs rounded-xl">
                Close
            </button>
        </div>
    </div>
</div>
@endsection
