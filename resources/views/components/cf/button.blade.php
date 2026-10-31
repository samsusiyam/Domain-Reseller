@props([
    'variant' => 'secondary', // primary, secondary, ghost, danger
    'size' => 'md', // sm (32px), md (36px)
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'icon' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center gap-1.5 font-medium transition-colors duration-150 rounded-[6px] border select-none disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-[#F6821F]/25';

$sizeClasses = match($size) {
    'sm' => 'h-8 px-2.5 text-[12px] leading-tight',
    default => 'h-9 px-3 text-[13px] leading-tight',
};

$variantClasses = match($variant) {
    'primary' => 'bg-[#F6821F] hover:bg-[#E07116] active:bg-[#C9620E] text-white border-[#F6821F]',
    'ghost' => 'bg-transparent hover:bg-[#F7F7F8] dark:hover:bg-[#1F2937] text-[#6B7280] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-[#F9FAFB] border-transparent',
    'danger' => 'bg-white dark:bg-[#111827] hover:bg-[#FEF2F2] dark:hover:bg-red-950/30 text-[#DC2626] border-[#E5E7EB] dark:border-[#374151] hover:border-[#DC2626]',
    default => 'bg-white dark:bg-[#111827] hover:bg-[#F7F7F8] dark:hover:bg-[#1F2937] text-[#111827] dark:text-[#F9FAFB] border-[#E5E7EB] dark:border-[#374151] hover:border-[#D1D5DB]',
};

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="shrink-0 text-current">{!! $icon !!}</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="shrink-0 text-current">{!! $icon !!}</span>
        @endif
        {{ $slot }}
    </button>
@endif
