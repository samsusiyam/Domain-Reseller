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
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $domain->domain_name }}</h1>
                <x-badge :color="$domain->status">{{ $domain->status }}</x-badge>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                <span>Registered: <strong>{{ $domain->registered_at ? $domain->registered_at->format('M d, Y') : 'N/A' }}</strong></span>
                <span>•</span>
                <span>Expires: <strong class="{{ $domain->expires_at && $domain->expires_at->isPast() ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">{{ $domain->expires_at ? $domain->expires_at->format('M d, Y') : 'N/A' }}</strong></span>
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
                <select name="years" class="px-3 py-2 bg-slate-50 dark:bg-slate-800/50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-[#F6821F]">
                    <option value="1">1 Year</option>
                    <option value="2">2 Years</option>
                    <option value="3">3 Years</option>
                    <option value="5">5 Years</option>
                </select>
                <button type="submit" class="px-4 py-2 cf-btn cf-btn-primary cf-btn-sm text-xs font-medium transition shadow">
                    Renew Now
                </button>
            </form>

            <button type="button" @click="fetchEpp()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                Get EPP Code
            </button>
        </div>
    </div>

    <x-domain-tabs :domain="$domain" active="overview" />

    <!-- Security Toggles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Registrar Lock Card -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Registrar Transfer Lock</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Prevents unauthorized domain transfers out.</p>
            </div>
            <button type="button" @click="toggleLock()"
                :class="isLocked ? 'bg-[#F6821F]' : 'bg-slate-200'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                <span :class="isLocked ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white dark:bg-slate-900 shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>

        <!-- WHOIS Privacy Card -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">WHOIS ID Protection</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Hides your personal identity from public WHOIS.</p>
            </div>
            <button type="button" @click="togglePrivacy()"
                :class="isPrivate ? 'bg-[#F6821F]' : 'bg-slate-200'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                <span :class="isPrivate ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white dark:bg-slate-900 shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>
    </div>

    <!-- Nameservers Snapshot -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Current Authoritative Nameservers</h3>
            <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="text-xs font-semibold text-[#F6821F] hover:underline">Change Nameservers &rarr;</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 font-mono text-xs text-slate-800">
            @forelse($domain->nameservers ?? [] as $ns)
                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-xl">{{ $ns }}</div>
            @empty
                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-400">Default Nameservers</div>
            @endforelse
        </div>
    </div>

    <!-- EPP Modal -->
    <div x-show="eppModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="eppModal = false" class="bg-white dark:bg-slate-900 rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">EPP / Authorization Code</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">This authorization code is required if you wish to transfer {{ $domain->domain_name }} to another registrar.</p>

            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 text-center font-mono text-base font-bold text-[#F6821F] select-all mb-4">
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
