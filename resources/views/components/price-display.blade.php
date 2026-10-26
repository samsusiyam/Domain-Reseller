@props(['amount', 'showBdt' => true])

@php
$amount = (float) $amount;
$currencyConverter = app(\App\Services\Currency\CurrencyConverter::class);
$amountBdt = $currencyConverter->convert($amount, 'USD', 'BDT');
@endphp

<span class="inline-flex items-baseline gap-1.5 font-bold text-slate-900 dark:text-white">
    <span class="text-base text-indigo-600 dark:text-indigo-400 font-extrabold">${{ number_format($amount, 2) }}</span>
    @if($showBdt)
        <span class="text-xs font-normal text-slate-500 dark:text-slate-400">/ ৳{{ number_format($amountBdt, 2) }} BDT</span>
    @endif
</span>
