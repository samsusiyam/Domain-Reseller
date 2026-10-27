@extends('layouts.guest', ['title' => 'Domain Availability Search'])

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14" x-data="{
    searchTab: 'single',
    query: '{{ request('query', request('domain', '')) }}',
    bulkDomains: '',
    checking: false,
    singleResult: null,
    bulkResults: [],
    error: null,
    toast: { show: false, message: '', domain: '' },

    init() {
        if (this.query) { this.checkSingle(); }
    },

    showToast(msg, domain) {
        this.toast = { show: true, message: msg, domain: domain };
        setTimeout(() => { this.toast.show = false; }, 4000);
    },

    updateCartBadge(count) {
        let badge = document.getElementById('header-cart-badge');
        if (badge) {
            badge.innerText = count;
            if (count > 0) badge.classList.remove('hidden');
            else badge.classList.add('hidden');
        }
    },

    addToCart(domain, action, buyNow = false) {
        let formData = new FormData();
        formData.append('domain', domain);
        formData.append('action', action || 'register');
        formData.append('years', 1);
        if (buyNow) formData.append('buy_now', 1);

        fetch('{{ route('cart.add') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => {
            if (buyNow) {
                window.location.href = '{{ route('checkout.index') }}';
                return;
            }
            return res.json();
        })
        .then(data => {
            if (!data) return;
            if (data.success) {
                this.showToast(data.message || (domain + ' added to cart.'), domain);
                if (data.count !== undefined) {
                    this.updateCartBadge(data.count);
                }
            } else {
                alert(data.message || 'Could not add domain to cart.');
            }
        })
        .catch(err => {
            alert('Failed to update cart.');
        });
    },

    checkSingle() {
        let q = this.query.trim();
        if (!q) return;
        if (!q.includes('.')) {
            q = q + '{{ request('tld', '.com') }}';
            this.query = q;
        }
        this.checking = true;
        this.singleResult = null;
        this.error = null;

        fetch('{{ route('domain.check') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ domain: q })
        })
        .then(res => res.json())
        .then(data => {
            this.checking = false;
            if (data.success) {
                this.singleResult = data.data;
                this.$nextTick(() => {
                    if (window.formatCurrencyElements) window.formatCurrencyElements();
                });
            } else {
                this.error = data.message || 'Availability check failed.';
            }
        })
        .catch(err => {
            this.checking = false;
            this.error = 'Failed to connect to registrar search engine.';
        });
    },

    checkBulk() {
        let lines = this.bulkDomains.split('\n').map(d => d.trim()).filter(d => d.length > 0);
        if (lines.length === 0) return;
        if (lines.length > 20) {
            this.error = 'Please enter at most 20 domains at a time.';
            return;
        }
        this.checking = true;
        this.bulkResults = [];
        this.error = null;

        fetch('{{ route('domain.check.bulk') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ domains: lines })
        })
        .then(res => res.json())
        .then(data => {
            this.checking = false;
            if (data.success) {
                this.bulkResults = Object.values(data.data);
                this.$nextTick(() => {
                    if (window.formatCurrencyElements) window.formatCurrencyElements();
                });
            } else {
                this.error = data.message || 'Bulk lookup failed.';
            }
        })
        .catch(err => {
            this.checking = false;
            this.error = 'Bulk search failed.';
        });
    }
}" x-init="if (query) { checkSingle(); }">

    <!-- Global Floating Cart Toast (Non-blocking, fixed bottom right) -->
    <div x-show="toast.show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="fixed bottom-6 right-6 z-50 max-w-sm w-full p-4 rounded-xl shadow-2xl backdrop-blur-md flex items-center gap-3 transition-all"
         style="background-color: var(--surface, #1e293b); border: 1px solid var(--border); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);"
         role="status"
         aria-live="polite">
        <div class="w-8 h-8 rounded-full bg-[#16A34A]/15 text-[#16A34A] flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>
        <div class="flex-1 min-w-0 text-xs">
            <p class="font-semibold" style="color: var(--text);" x-text="toast.message"></p>
            <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Added to your shopping session</p>
        </div>
        <a href="{{ route('cart.index') }}"
           class="px-3 py-1.5 text-xs font-semibold text-white bg-[#F6821F] hover:bg-[#E57210] active:bg-[#D56508] rounded-lg shrink-0 shadow-sm transition-all flex items-center gap-1 cursor-pointer">
            <span>View cart</span>
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
        <button type="button" @click="toast.show = false" class="text-gray-400 hover:text-gray-200 p-1 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Header Section -->
    <div class="mb-8">
        <div class="cf-breadcrumb mb-2">
            <a href="{{ route('home') }}">Home</a>
            <span class="cf-breadcrumb-sep">/</span>
            <span class="cf-breadcrumb-current">Domain Search</span>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">Domain availability search</h1>
        <p class="text-sm mt-1" style="color: var(--text-muted);">
            Real-time wholesale registration check backed by upstream registries.
        </p>
    </div>

    <!-- Search Controls Container -->
    <div class="cf-panel p-6 mb-8">
        <!-- Tabs: Single vs Bulk -->
        <div class="cf-tabs mb-5">
            <button @click="searchTab = 'single'"
                    type="button"
                    class="cf-tab-item"
                    :class="searchTab === 'single' ? 'active' : ''">
                Single domain search
            </button>
            <button @click="searchTab = 'bulk'"
                    type="button"
                    class="cf-tab-item"
                    :class="searchTab === 'bulk' ? 'active' : ''">
                Bulk search (up to 20)
            </button>
        </div>

        <!-- Single Search Form -->
        <div x-show="searchTab === 'single'">
            <form @submit.prevent="checkSingle()" class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <input type="text"
                           x-model="query"
                           placeholder="Enter domain name (e.g. cloudapp.com, mycompany.net)"
                           required
                           class="cf-input h-10 font-mono text-sm">
                </div>
                <button type="submit"
                        :disabled="checking"
                        class="cf-btn cf-btn-primary h-10 px-5 font-medium text-sm">
                    <span x-show="!checking">Search</span>
                    <span x-show="checking" x-cloak class="flex items-center gap-1.5">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Checking...</span>
                    </span>
                </button>
            </form>

            <!-- Quick TLD Shortcuts -->
            <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs" style="color: var(--text-muted);">
                <span>Filter by extension:</span>
                @foreach(['.com', '.net', '.org', '.xyz', '.io', '.co'] as $ext)
                    <button type="button"
                            @click="query = (query.includes('.') ? query.split('.')[0] : (query || 'example')) + '{{ $ext }}'; checkSingle();"
                            class="px-2 py-0.5 rounded-[4px] border font-mono text-[11px] hover:border-[#F6821F] transition"
                            style="background-color: var(--surface); border-color: var(--border); color: var(--text);">
                        {{ $ext }}
                    </button>
                @endforeach
            </div>

            <!-- Single Result Panel -->
            <div x-show="singleResult" x-cloak class="mt-6 p-5 rounded-[6px] border" style="background-color: var(--surface); border-color: var(--border);">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-xl font-semibold font-mono tracking-tight" style="color: var(--text);" x-text="singleResult?.domain"></span>

                            <!-- Status: Available -->
                            <template x-if="singleResult?.status === 'available' || (singleResult?.is_available && singleResult?.status !== 'error')">
                                <span class="cf-status-dot success">Available</span>
                            </template>

                            <!-- Status: Taken -->
                            <template x-if="singleResult?.status === 'taken' || (!singleResult?.is_available && singleResult?.status !== 'error')">
                                <span class="cf-status-dot neutral">Registered</span>
                            </template>

                            <!-- Status: Error (Never display 'Available' on error) -->
                            <template x-if="singleResult?.status === 'error'">
                                <span class="cf-status-dot neutral">Unable to check, try again</span>
                            </template>

                            <!-- Sandbox indicator -->
                            <template x-if="singleResult?.is_test_mode">
                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 border border-amber-300">
                                    Sandbox mode
                                </span>
                            </template>
                        </div>

                        <!-- Explanatory text -->
                        <template x-if="singleResult?.status === 'available'">
                            <p class="text-xs mt-1" style="color: var(--text-muted);">
                                Available for immediate wholesale registration. Rate:
                                <span class="font-semibold font-mono text-sm" style="color: var(--text);">
                                    <span class="price tabular-nums" :data-usd="singleResult?.registration_cost">
                                        $<span x-text="singleResult?.registration_cost"></span>/yr
                                    </span>
                                </span>
                            </p>
                        </template>

                        <template x-if="singleResult?.status === 'taken'">
                            <p class="text-xs mt-1" style="color: var(--text-muted);">
                                This domain is already registered. You may inspect the public WHOIS registry record or check alternative extensions.
                            </p>
                        </template>

                        <template x-if="singleResult?.status === 'error'">
                            <p class="text-xs mt-1" style="color: var(--text-muted);" x-text="singleResult?.error_message || 'Upstream registry did not respond within timeout.'"></p>
                        </template>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <!-- If Available: Add to Cart + Buy Now -->
                        <template x-if="singleResult?.status === 'available' || (singleResult?.is_available && singleResult?.status !== 'error')">
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="addToCart(singleResult.domain, 'register', false)"
                                        class="cf-btn cf-btn-secondary h-9 px-4 text-xs">
                                    Add to cart
                                </button>
                                <button type="button"
                                        @click="addToCart(singleResult.domain, 'register', true)"
                                        class="cf-btn cf-btn-primary h-9 px-4 text-xs font-medium">
                                    Buy now
                                </button>
                            </div>
                        </template>

                        <!-- If Taken: WHOIS link -->
                        <template x-if="singleResult?.status === 'taken'">
                            <a :href="'{{ route('whois.index') }}?domain=' + encodeURIComponent(singleResult.domain)"
                               class="cf-btn cf-btn-secondary h-9 px-4 text-xs">
                                Inspect WHOIS
                            </a>
                        </template>

                        <!-- If Error: Retry -->
                        <template x-if="singleResult?.status === 'error'">
                            <button type="button"
                                    @click="checkSingle()"
                                    class="cf-btn cf-btn-secondary h-9 px-4 text-xs">
                                Retry check
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Search Form -->
        <div x-show="searchTab === 'bulk'" x-cloak>
            <form @submit.prevent="checkBulk()" class="space-y-3">
                <textarea x-model="bulkDomains"
                          rows="6"
                          placeholder="Enter up to 20 domains (one per line):&#10;example1.com&#10;example2.net&#10;example3.org"
                          required
                          class="cf-textarea font-mono text-xs"></textarea>
                <div class="flex items-center justify-between">
                    <span class="text-xs" style="color: var(--text-muted);">Max 20 domains per check</span>
                    <button type="submit"
                            :disabled="checking"
                            class="cf-btn cf-btn-primary h-10 px-6 font-medium text-sm">
                        <span x-show="!checking">Check all</span>
                        <span x-show="checking" x-cloak>Checking...</span>
                    </button>
                </div>
            </form>

            <!-- Bulk Results Table -->
            <div x-show="bulkResults.length > 0" x-cloak class="mt-6 cf-table-wrapper">
                <table class="cf-table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Status</th>
                            <th class="align-right">Wholesale rate</th>
                            <th class="align-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="r in bulkResults" :key="r.domain">
                            <tr>
                                <td class="font-mono font-medium" x-text="r.domain"></td>
                                <td>
                                    <template x-if="r.status === 'available' || (r.is_available && r.status !== 'error')">
                                        <span class="cf-status-dot success">Available</span>
                                    </template>
                                    <template x-if="r.status === 'taken' || (!r.is_available && r.status !== 'error')">
                                        <span class="cf-status-dot neutral">Registered</span>
                                    </template>
                                    <template x-if="r.status === 'error'">
                                        <span class="cf-status-dot neutral">Unable to check</span>
                                    </template>
                                </td>
                                <td class="align-right font-mono tabular-nums">
                                    <template x-if="r.status === 'available' || r.is_available">
                                        <span class="price" :data-usd="r.registration_cost">
                                            $<span x-text="r.registration_cost"></span>/yr
                                        </span>
                                    </template>
                                    <template x-if="r.status !== 'available' && !r.is_available">
                                        <span style="color: var(--text-muted);">-</span>
                                    </template>
                                </td>
                                <td class="align-right">
                                    <template x-if="r.status === 'available' || (r.is_available && r.status !== 'error')">
                                        <button type="button"
                                                @click="addToCart(r.domain, 'register', false)"
                                                class="cf-btn cf-btn-secondary cf-btn-sm">
                                            Add to cart
                                        </button>
                                    </template>
                                    <template x-if="r.status === 'taken'">
                                        <a :href="'{{ route('whois.index') }}?domain=' + encodeURIComponent(r.domain)"
                                           class="cf-btn cf-btn-ghost cf-btn-sm">
                                            WHOIS
                                        </a>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TLD Pricing Quick Matrix Reference -->
    @if(isset($allTlds) && $allTlds->isNotEmpty())
        <div class="cf-table-wrapper">
            <div class="cf-panel-header">
                <span class="font-semibold text-xs uppercase tracking-wider" style="color: var(--text-muted);">
                    Popular Wholesale Extensions
                </span>
                <a href="{{ route('pricing') }}" class="text-xs hover:underline" style="color: var(--accent);">View full matrix →</a>
            </div>
            <table class="cf-table">
                <thead>
                    <tr>
                        <th>TLD Extension</th>
                        <th>Registrar</th>
                        <th class="align-right">Register (1 yr)</th>
                        <th class="align-right">Renew (1 yr)</th>
                        <th class="align-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allTlds->take(6) as $tld)
                        <tr>
                            <td class="font-mono font-semibold" style="color: var(--text);">{{ $tld->tld }}</td>
                            <td class="text-xs" style="color: var(--text-muted);">{{ $tld->registrar?->name ?? 'Tier 1' }}</td>
                            <td class="align-right font-mono tabular-nums">
                                <x-price :usd="$tld->client_register" />
                            </td>
                            <td class="align-right font-mono tabular-nums">
                                <x-price :usd="$tld->client_renew" />
                            </td>
                            <td class="align-right">
                                <button type="button"
                                        @click="query = 'mybrand{{ $tld->tld }}'; searchTab = 'single'; checkSingle();"
                                        class="cf-btn cf-btn-ghost cf-btn-sm" style="color: var(--accent);">
                                    Search
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
