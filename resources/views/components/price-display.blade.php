@props(['amount', 'showBdt' => true])

@php
$amount = (float) $amount;
$currencyConverter = app(\App\Services\Currency\CurrencyConverter::class);
$amountBdt = $currencyConverter->convert($amount, 'USD', 'BDT');
$currency = session('currency', 'USD');
@endphp

@if($currency === 'BDT')
    <span class="inline-flex items-baseline gap-1 font-mono tabular-nums text-[#111827]">
        <span class="font-semibold text-[14px]">৳{{ number_format($amountBdt, 2) }}</span>
        @if($showBdt)
            <span class="text-[12px] text-[#6B7280]">(${{ number_format($amount, 2) }})</span>
        @endif
    </span>
@else
    <span class="inline-flex items-baseline gap-1 font-mono tabular-nums text-[#111827]">
        <span class="font-semibold text-[14px]">${{ number_format($amount, 2) }}</span>
        @if($showBdt)
            <span class="text-[12px] text-[#6B7280]">(৳{{ number_format($amountBdt, 2) }})</span>
        @endif
    </span>
@endif
