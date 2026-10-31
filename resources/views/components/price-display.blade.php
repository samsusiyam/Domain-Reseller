@props([
    'amount' => null,
    'amountUsd' => null,
    'usd' => null,
    'showBdt' => true,
    'class' => ''
])

@php
    $amount = (float) ($amount ?? $amountUsd ?? $usd ?? 0);
    $currencyConverter = app(\App\Services\Currency\CurrencyConverter::class);
    $rate = $currencyConverter->getRate('BDT');
    $amountBdt = round($amount * $rate, 2);
    $currency = session('currency', request()->cookie('currency', 'USD'));
@endphp

@if($currency === 'BDT')
    <span class="inline-flex items-baseline gap-1 font-mono tabular-nums text-slate-900 dark:text-white {{ $class }}">
        <span class="font-semibold text-[14px]">৳{{ number_format($amountBdt, 2) }}</span>
        @if($showBdt)
            <span class="text-[12px] text-slate-500 dark:text-slate-400">(${{ number_format($amount, 2) }})</span>
        @endif
    </span>
@else
    <span class="inline-flex items-baseline gap-1 font-mono tabular-nums text-slate-900 dark:text-white {{ $class }}">
        <span class="font-semibold text-[14px]">${{ number_format($amount, 2) }}</span>
        @if($showBdt)
            <span class="text-[12px] text-slate-500 dark:text-slate-400">(৳{{ number_format($amountBdt, 2) }})</span>
        @endif
    </span>
@endif
