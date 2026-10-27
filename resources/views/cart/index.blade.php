@extends('layouts.guest', ['title' => 'Shopping Cart'])

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14" x-data="{
    loading: false,
    showConfirmModal: false,
    confirmAction: null,
    confirmTitle: '',
    confirmMessage: '',
    confirmButtonText: 'Confirm',

    openRemoveModal(domain) {
        this.confirmTitle = 'Remove from Cart';
        this.confirmMessage = 'Are you sure you want to remove \'' + domain + '\' from your shopping cart?';
        this.confirmButtonText = 'Remove Domain';
        this.confirmAction = () => this.executeRemove(domain);
        this.showConfirmModal = true;
    },

    openClearModal() {
        this.confirmTitle = 'Clear Shopping Cart';
        this.confirmMessage = 'Are you sure you want to remove all domains from your shopping cart?';
        this.confirmButtonText = 'Clear All';
        this.confirmAction = () => { window.location.href = '{{ route('domain.search') }}'; };
        this.showConfirmModal = true;
    },

    executeRemove(domain) {
        this.loading = true;
        this.showConfirmModal = false;
        fetch('{{ url('/cart/items') }}/' + encodeURIComponent(domain), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            window.location.reload();
        })
        .catch(() => {
            this.loading = false;
        });
    },

    updateYears(domain, years) {
        this.loading = true;
        fetch('{{ url('/cart/items') }}/' + encodeURIComponent(domain), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ years: parseInt(years) })
        })
        .then(res => res.json())
        .then(data => {
            window.location.reload();
        })
        .catch(() => {
            this.loading = false;
        });
    }
}">

    <!-- Page Title -->
    <div class="mb-8">
        <div class="cf-breadcrumb mb-2">
            <a href="{{ route('home') }}">Home</a>
            <span class="cf-breadcrumb-sep">/</span>
            <span class="cf-breadcrumb-current">Cart</span>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">Shopping cart</h1>
        <p class="text-sm mt-1" style="color: var(--text-muted);">
            Review selected domains, configure registration periods, and apply discount promo codes.
        </p>
    </div>

    @if(!empty($warnings))
        <div class="mb-6 space-y-2">
            @foreach($warnings as $w)
                <div class="p-3 rounded-[6px] text-xs flex items-center gap-2 font-medium"
                     style="background-color: var(--warning-bg); border: 1px solid var(--warning); color: var(--warning);">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span>{{ $w }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if(empty($items))
        <!-- Empty Cart State -->
        <div class="cf-panel p-12 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center" style="background-color: var(--surface); color: var(--text-muted);">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>
            <h2 class="text-base font-semibold" style="color: var(--text);">Your cart is empty</h2>
            <p class="text-xs mt-1 mb-6 max-w-sm mx-auto" style="color: var(--text-muted);">
                You have no domains queued for registration or renewal. Search our registry catalog to find wholesale domain names.
            </p>
            <a href="{{ route('domain.search') }}" class="cf-btn cf-btn-primary h-10 px-5 font-medium text-sm">
                Find domains to register
            </a>
        </div>
    @else
        <!-- Cart Layout: Items Table (Left 2/3) + Summary Box (Right 1/3) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left: Items List -->
            <div class="lg:col-span-8 cf-panel overflow-hidden">
                <div class="cf-panel-header">
                    <span class="font-semibold text-xs uppercase tracking-wider" style="color: var(--text-muted);">
                        Domain Items ({{ count($items) }})
                    </span>
                    <button type="button" @click="openClearModal()" class="text-xs hover:underline cursor-pointer" style="color: var(--danger);">
                        Clear all
                    </button>
                </div>

                <div class="divide-y" style="border-color: var(--border);">
                    @foreach($items as $item)
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-base font-semibold truncate" style="color: var(--text);">
                                        {{ $item['domain'] }}
                                    </span>
                                    <span class="text-[11px] font-mono px-2 py-0.5 rounded uppercase" style="background-color: var(--surface); color: var(--text-muted); border: 1px solid var(--border);">
                                        {{ $item['action'] }}
                                    </span>
                                </div>
                                <div class="text-xs flex items-center gap-3" style="color: var(--text-muted);">
                                    <span>Rate: <x-price :usd="$item['unit_price']" /> / yr</span>
                                    <span>•</span>
                                    <span>ICANN & Failover Protected</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 shrink-0 w-full sm:w-auto justify-between sm:justify-end">
                                <!-- Period Selector -->
                                <div class="flex items-center gap-1.5">
                                    <label class="text-xs" style="color: var(--text-muted);">Period:</label>
                                    <select class="cf-select h-8 text-xs py-0 px-2 w-24"
                                            @change="updateYears('{{ $item['domain'] }}', $event.target.value)">
                                        @for($y = 1; $y <= 10; $y++)
                                            <option value="{{ $y }}" {{ ($item['years'] ?? 1) == $y ? 'selected' : '' }}>
                                                {{ $y }} {{ $y === 1 ? 'Year' : 'Years' }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <!-- Item Total Price -->
                                <div class="text-right min-w-[70px]">
                                    <span class="font-semibold text-sm font-mono tabular-nums" style="color: var(--text);">
                                        <x-price :usd="$item['total_price']" />
                                    </span>
                                </div>

                                <!-- Remove Button -->
                                <button type="button"
                                        @click="openRemoveModal('{{ $item['domain'] }}')"
                                        class="p-1.5 rounded hover:bg-[var(--surface)] text-[var(--text-muted)] hover:text-[#DC2626] transition cursor-pointer"
                                        title="Remove domain">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Order Summary Panel -->
            <div class="lg:col-span-4 space-y-4">
                <div class="cf-panel p-5">
                    <h2 class="font-semibold text-sm mb-4" style="color: var(--text);">Order summary</h2>

                    <div class="space-y-2.5 text-xs pb-4 border-b" style="border-color: var(--border);">
                        <div class="flex justify-between">
                            <span style="color: var(--text-muted);">Subtotal ({{ $totals['item_count'] }} items)</span>
                            <span class="font-mono font-medium" style="color: var(--text);">
                                <x-price :usd="$totals['subtotal']" />
                            </span>
                        </div>

                        @if($totals['discount'] > 0)
                            <div class="flex justify-between font-medium text-[#16A34A]">
                                <span>Discount ({{ $totals['promo']['code'] ?? 'Promo' }})</span>
                                <span class="font-mono tabular-nums">
                                    -<x-price :usd="$totals['discount']" />
                                </span>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <span style="color: var(--text-muted);">Registry taxes & ICANN fee</span>
                            <span class="font-mono" style="color: var(--text);">Included</span>
                        </div>
                    </div>

                    <div class="py-4 flex justify-between items-baseline">
                        <span class="text-sm font-semibold" style="color: var(--text);">Total</span>
                        <div class="text-right">
                            <span class="text-xl font-bold font-mono tracking-tight" style="color: var(--text);">
                                <x-price :usd="$totals['total']" />
                            </span>
                        </div>
                    </div>

                    <!-- Checkout CTA Button -->
                    @if($isValid)
                        <a href="{{ route('checkout.index') }}"
                           class="cf-btn cf-btn-primary w-full h-10 font-medium text-sm flex items-center justify-center gap-1.5">
                            <span>Proceed to checkout</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @else
                        <button type="button" disabled class="cf-btn cf-btn-primary w-full h-10 opacity-50 cursor-not-allowed">
                            Resolve unavailable items to proceed
                        </button>
                    @endif
                </div>

                <!-- Promo Code Panel -->
                <div class="cf-panel p-4">
                    <span class="block text-xs font-medium mb-2" style="color: var(--text);">Promotional discount code</span>
                    @if(!empty($totals['promo']))
                        <div class="flex items-center justify-between p-2 rounded-[4px] border" style="background-color: var(--surface); border-color: var(--border);">
                            <div>
                                <span class="font-mono font-semibold text-xs text-[#16A34A]">{{ $totals['promo']['code'] }}</span>
                                <span class="text-[11px] block" style="color: var(--text-muted);">
                                    {{ $totals['promo']['discount_type'] === 'percentage' ? $totals['promo']['discount_value'] . '% off' : '$' . $totals['promo']['discount_value'] . ' discount' }}
                                </span>
                            </div>
                            <form action="{{ route('cart.promo.remove') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs hover:underline cursor-pointer" style="color: var(--danger);">Remove</button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('cart.promo.apply') }}" method="POST" class="flex gap-1.5">
                            @csrf
                            <input type="text"
                                   name="code"
                                   placeholder="e.g. WHOLESALE10"
                                   required
                                   class="cf-input h-8 text-xs font-mono uppercase">
                            <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm shrink-0">
                                Apply
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Secure Guarantee Note -->
                <div class="text-[11px] text-center space-y-1" style="color: var(--text-muted);">
                    <div class="flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#16A34A]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                        </svg>
                        <span>Encrypted checkout with upstream registrar failover</span>
                    </div>
                </div>

            </div>

        </div>
    @endif

    <!-- Sleek Custom Confirmation Modal (Linear / Tailwind style) -->
    <div x-show="showConfirmModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop with smooth blur -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
             x-show="showConfirmModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showConfirmModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6"
                 style="background-color: var(--surface, #1e293b); border: 1px solid var(--border);"
                 x-show="showConfirmModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.stop>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold" style="color: var(--text);" x-text="confirmTitle"></h3>
                        <p class="text-xs mt-1.5 leading-relaxed" style="color: var(--text-muted);" x-text="confirmMessage"></p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button"
                            @click="showConfirmModal = false"
                            class="cf-btn cf-btn-secondary px-4 py-2 text-xs font-medium rounded-lg cursor-pointer">
                        Cancel
                    </button>
                    <button type="button"
                            @click="if (confirmAction) confirmAction()"
                            class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 active:bg-red-800 rounded-lg shadow-sm transition-all cursor-pointer"
                            x-text="confirmButtonText">
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
