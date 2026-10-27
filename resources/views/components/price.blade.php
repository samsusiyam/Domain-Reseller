@props([
    'amount',
    'class' => '',
    'tabular' => true,
    'showEquivalent' => false
])

@php
    $amount = (float) $amount;
    $currency = session('currency', request()->cookie('currency', 'USD'));
    $converter = app(\App\Services\Currency\CurrencyConverter::class);
    $rate = $converter->getRate('BDT');
    $bdtAmount = round($amount * $rate);

    if ($currency === 'BDT') {
        $formatted = '৳' . number_format($bdtAmount);
        if ($showEquivalent) {
            $formatted .= ' <span class="text-[12px] text-[#6B7280] font-normal tabular-nums">($' . number_format($amount, 2) . ')</span>';
        }
    } else {
        $formatted = '$' . number_format($amount, 2);
        if ($showEquivalent) {
            $formatted .= ' <span class="text-[12px] text-[#6B7280] font-normal tabular-nums">(৳' . number_format($bdtAmount) . ')</span>';
        }
    }
@endphp

<span class="price {{ $tabular ? 'tabular-nums' : '' }} {{ $class }}"
      data-usd="{{ number_format($amount, 2, '.', '') }}"
      @if($showEquivalent) data-show-equivalent="true" @endif>{!! $formatted !!}</span>
