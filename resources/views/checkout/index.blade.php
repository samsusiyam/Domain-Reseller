@extends('layouts.guest', ['title' => 'Checkout'])

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14" x-data="{
    paymentMethod: 'balance',
    contactType: '{{ $contacts->isNotEmpty() ? 'existing' : 'new' }}',
    customNs: false,
    bdtRate: {{ $bdtRate }},
    totalUsd: {{ $totals['total'] }},
    get totalBdt() {
        return (this.totalUsd * this.bdtRate).toFixed(2);
    }
}">

    <!-- Page Title -->
    <div class="mb-8">
        <div class="cf-breadcrumb mb-2">
            <a href="{{ route('home') }}">Home</a>
            <span class="cf-breadcrumb-sep">/</span>
            <a href="{{ route('cart.index') }}">Cart</a>
            <span class="cf-breadcrumb-sep">/</span>
            <span class="cf-breadcrumb-current">Checkout</span>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">Checkout</h1>
        <p class="text-sm mt-1" style="color: var(--text-muted);">
            Provide registrant contact information, select authoritative nameservers, and confirm payment.
        </p>
    </div>

    @if(session('error'))
        <div class="mb-6 p-3.5 rounded-[6px] text-xs flex items-center justify-between font-medium"
             style="background-color: var(--danger-bg); border: 1px solid var(--danger); color: var(--danger);">
            <span>{{ session('error') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="hover:opacity-75">✕</button>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left: Steps (8 cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- STEP 1: Registrant Contact -->
                <div class="cf-panel p-6">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b" style="border-color: var(--border);">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-[4px] bg-[#F6821F] text-white flex items-center justify-center font-bold text-xs">1</span>
                            <h2 class="font-semibold text-sm" style="color: var(--text);">Registrant contact</h2>
                        </div>
                        <span class="text-xs" style="color: var(--text-muted);">ICANN WHOIS Profile</span>
                    </div>

                    @if($contacts->isNotEmpty())
                        <div class="mb-4 flex items-center gap-4 text-xs">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="contact_type" value="existing" x-model="contactType" class="accent-[#F6821F]">
                                <span>Use saved profile</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="contact_type" value="new" x-model="contactType" class="accent-[#F6821F]">
                                <span>Add new profile</span>
                            </label>
                        </div>

                        <div x-show="contactType === 'existing'" class="mb-4">
                            <select name="contact_id" class="cf-select h-10">
                                @foreach($contacts as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->first_name }} {{ $c->last_name }} ({{ $c->email }} • {{ $c->city }}, {{ $c->country }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="contact_type" value="new">
                    @endif

                    <div x-show="contactType === 'new'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">First name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', explode(' ', $user->name)[0] ?? '') }}" class="cf-input h-10">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Last name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', explode(' ', $user->name, 2)[1] ?? '') }}" class="cf-input h-10">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Email address</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="cf-input h-10">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Phone number</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '+8801700000000') }}" placeholder="+880 1700 000000" class="cf-input h-10">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Address</label>
                            <input type="text" name="address_1" value="{{ old('address_1', $user->address_line_1 ?? 'House 12, Road 4') }}" class="cf-input h-10">
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">City</label>
                                <input type="text" name="city" value="{{ old('city', $user->city ?? 'Dhaka') }}" class="cf-input h-10">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Country</label>
                                <select name="country" class="cf-select h-10">
                                    <option value="BD" {{ ($user->country ?? 'BD') === 'BD' ? 'selected' : '' }}>Bangladesh (BD)</option>
                                    <option value="US" {{ ($user->country ?? '') === 'US' ? 'selected' : '' }}>United States (US)</option>
                                    <option value="GB" {{ ($user->country ?? '') === 'GB' ? 'selected' : '' }}>United Kingdom (GB)</option>
                                    <option value="CA" {{ ($user->country ?? '') === 'CA' ? 'selected' : '' }}>Canada (CA)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Postal code</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code ?? '1200') }}" class="cf-input h-10">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Nameservers -->
                <div class="cf-panel p-6">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b" style="border-color: var(--border);">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-[4px] bg-[#F6821F] text-white flex items-center justify-center font-bold text-xs">2</span>
                            <h2 class="font-semibold text-sm" style="color: var(--text);">Nameservers (DNS)</h2>
                        </div>
                        <label class="flex items-center gap-2 text-xs cursor-pointer" style="color: var(--text-muted);">
                            <input type="checkbox" x-model="customNs" class="rounded accent-[#F6821F]">
                            <span>Use custom nameservers</span>
                        </label>
                    </div>

                    <div x-show="!customNs" class="text-xs p-3 rounded-[6px] border space-y-1 font-mono" style="background-color: var(--surface); border-color: var(--border); color: var(--text);">
                        <div>Primary: {{ $defaultNs[0] ?? 'ns1.domainreseller.local' }}</div>
                        <div>Secondary: {{ $defaultNs[1] ?? 'ns2.domainreseller.local' }}</div>
                        <input type="hidden" name="ns1" value="{{ $defaultNs[0] ?? 'ns1.domainreseller.local' }}">
                        <input type="hidden" name="ns2" value="{{ $defaultNs[1] ?? 'ns2.domainreseller.local' }}">
                    </div>

                    <div x-show="customNs" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Nameserver 1</label>
                            <input type="text" name="ns1" placeholder="ns1.yourhost.com" class="cf-input h-10 font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Nameserver 2</label>
                            <input type="text" name="ns2" placeholder="ns2.yourhost.com" class="cf-input h-10 font-mono text-xs">
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Payment Method -->
                <div class="cf-panel p-6">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b" style="border-color: var(--border);">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-[4px] bg-[#F6821F] text-white flex items-center justify-center font-bold text-xs">3</span>
                            <h2 class="font-semibold text-sm" style="color: var(--text);">Payment method</h2>
                        </div>
                        <span class="text-xs" style="color: var(--text-muted);">Instant activation</span>
                    </div>

                    <div class="space-y-3">

                        <!-- Method 1: Account Balance -->
                        <label class="flex items-start gap-3 p-3.5 rounded-[6px] border cursor-pointer transition"
                               :style="paymentMethod === 'balance' ? 'border-color: var(--accent); background-color: var(--accent-subtle);' : 'border-color: var(--border); background-color: var(--bg);'">
                            <input type="radio" name="payment_method" value="balance" x-model="paymentMethod" class="mt-0.5 accent-[#F6821F]">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-xs" style="color: var(--text);">Account credit balance</span>
                                    <span class="font-mono font-semibold text-xs tabular-nums" style="color: var(--text);">
                                        ${{ number_format($user->balance, 2) }}
                                    </span>
                                </div>
                                <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">
                                    @if($user->hasSufficientBalance((float) $totals['total']))
                                        <span class="text-[#16A34A] font-medium">✓ Sufficient balance for one-click payment</span>
                                    @else
                                        <span class="text-[#DC2626]">Insufficient balance (requires ${{ number_format($totals['total'], 2) }}). Please choose another method.</span>
                                    @endif
                                </p>
                            </div>
                        </label>

                        <!-- Method 2: bKash -->
                        <label class="flex items-start gap-3 p-3.5 rounded-[6px] border cursor-pointer transition"
                               :style="paymentMethod === 'bkash' ? 'border-color: var(--accent); background-color: var(--accent-subtle);' : 'border-color: var(--border); background-color: var(--bg);'">
                            <input type="radio" name="payment_method" value="bkash" x-model="paymentMethod" class="mt-0.5 accent-[#F6821F]">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-xs flex items-center gap-2" style="color: var(--text);">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#E2136E] text-white">bKash</span>
                                        <span>bKash Payment Gateway</span>
                                    </span>
                                    <span class="font-mono font-semibold text-xs tabular-nums" style="color: var(--text);">
                                        ৳<span x-text="totalBdt"></span> BDT
                                    </span>
                                </div>
                                <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">
                                    Rate: 1 USD = {{ number_format($bdtRate, 2) }} BDT. Instant automated provisioning.
                                </p>
                            </div>
                        </label>

                        <!-- Method 3: Stripe -->
                        <label class="flex items-start gap-3 p-3.5 rounded-[6px] border cursor-pointer transition"
                               :style="paymentMethod === 'stripe' ? 'border-color: var(--accent); background-color: var(--accent-subtle);' : 'border-color: var(--border); background-color: var(--bg);'">
                            <input type="radio" name="payment_method" value="stripe" x-model="paymentMethod" class="mt-0.5 accent-[#F6821F]">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-xs flex items-center gap-2" style="color: var(--text);">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#635BFF] text-white">Stripe</span>
                                        <span>Credit / Debit Card (Visa, MasterCard, Amex)</span>
                                    </span>
                                    <span class="font-mono font-semibold text-xs tabular-nums" style="color: var(--text);">
                                        ${{ number_format($totals['total'], 2) }} USD
                                    </span>
                                </div>
                            </div>
                        </label>

                        <!-- Method 4: Binance Pay -->
                        <label class="flex items-start gap-3 p-3.5 rounded-[6px] border cursor-pointer transition"
                               :style="paymentMethod === 'binance' ? 'border-color: var(--accent); background-color: var(--accent-subtle);' : 'border-color: var(--border); background-color: var(--bg);'">
                            <input type="radio" name="payment_method" value="binance" x-model="paymentMethod" class="mt-0.5 accent-[#F6821F]">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-xs flex items-center gap-2" style="color: var(--text);">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#F3BA2F] text-black">Binance</span>
                                        <span>Binance Pay (USDT / Crypto)</span>
                                    </span>
                                    <span class="font-mono font-semibold text-xs tabular-nums" style="color: var(--text);">
                                        ${{ number_format($totals['total'], 2) }} USDT
                                    </span>
                                </div>
                            </div>
                        </label>

                        <!-- Method 5: Manual Bank Transfer -->
                        <label class="flex items-start gap-3 p-3.5 rounded-[6px] border cursor-pointer transition"
                               :style="paymentMethod === 'bank_transfer' ? 'border-color: var(--accent); background-color: var(--accent-subtle);' : 'border-color: var(--border); background-color: var(--bg);'">
                            <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="mt-0.5 accent-[#F6821F]">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-xs" style="color: var(--text);">Manual bank transfer / Offline deposit</span>
                                    <span class="text-[11px] font-mono px-2 py-0.5 rounded" style="background-color: var(--surface); color: var(--text-muted);">Admin review</span>
                                </div>
                                <div x-show="paymentMethod === 'bank_transfer'" x-cloak class="mt-3 p-3 rounded-[6px] border space-y-2 text-xs" style="background-color: var(--surface); border-color: var(--border);">
                                    <p class="font-medium" style="color: var(--text);">Deposit to Company Bank Account:</p>
                                    <div class="font-mono text-[11px] space-y-0.5" style="color: var(--text-muted);">
                                        <div>Bank: City Bank Bangladesh</div>
                                        <div>Account Name: Domain Reseller Registry Ltd</div>
                                        <div>Account No: 120-345678-001</div>
                                        <div>Routing: 225272635</div>
                                    </div>

                                    <div class="pt-2">
                                        <label class="block text-[11px] font-medium mb-1" style="color: var(--text);">Bank Deposit Transaction Reference / Slip ID *</label>
                                        <input type="text" name="bank_trx_id" placeholder="e.g. TRX-98721345" class="cf-input h-8 text-xs font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium mb-1" style="color: var(--text);">Notes or branch details (optional)</label>
                                        <input type="text" name="bank_notes" placeholder="Branch name or depositor note" class="cf-input h-8 text-xs">
                                    </div>
                                </div>
                            </div>
                        </label>

                    </div>
                </div>

            </div>

            <!-- Right: Order Summary (4 cols) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="cf-panel p-5">
                    <h3 class="font-semibold text-sm mb-3" style="color: var(--text);">Review order</h3>

                    <div class="divide-y text-xs pb-3 mb-3 border-b" style="border-color: var(--border);">
                        @foreach($items as $item)
                            <div class="py-2 flex items-center justify-between">
                                <div>
                                    <span class="font-mono font-medium block truncate max-w-[150px]" style="color: var(--text);">{{ $item['domain'] }}</span>
                                    <span class="text-[11px]" style="color: var(--text-muted);">{{ $item['years'] }} yr {{ $item['action'] }}</span>
                                </div>
                                <span class="font-mono tabular-nums font-semibold" style="color: var(--text);">
                                    <x-price :usd="$item['total_price']" />
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 text-xs pb-3 mb-3 border-b" style="border-color: var(--border);">
                        <div class="flex justify-between">
                            <span style="color: var(--text-muted);">Subtotal</span>
                            <span class="font-mono font-medium" style="color: var(--text);"><x-price :usd="$totals['subtotal']" /></span>
                        </div>
                        @if($totals['discount'] > 0)
                            <div class="flex justify-between text-[#16A34A] font-medium">
                                <span>Discount</span>
                                <span class="font-mono tabular-nums">-<x-price :usd="$totals['discount']" /></span>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between items-baseline mb-5">
                        <span class="text-sm font-semibold" style="color: var(--text);">Total amount</span>
                        <div class="text-right">
                            <span class="text-xl font-bold font-mono tracking-tight" style="color: var(--text);">
                                <x-price :usd="$totals['total']" />
                            </span>
                            <div class="text-[11px] font-mono" style="color: var(--text-muted);">
                                ~ ৳<span x-text="totalBdt"></span> BDT
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="cf-btn cf-btn-primary w-full h-11 font-medium text-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Authorize & submit order</span>
                    </button>
                </div>

                <div class="text-center text-[11px] space-y-1" style="color: var(--text-muted);">
                    <p>By submitting, you agree to the ICANN Registration agreement.</p>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
