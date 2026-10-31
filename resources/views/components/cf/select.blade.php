@props([
    'name' => null,
    'id' => null,
    'disabled' => false,
    'required' => false,
    'error' => null,
])

@php
$id = $id ?? $name;
$hasError = $error || ($name && $errors->has($name));
$errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full">
    <div class="relative">
        <select
            @if($name) name="{{ $name }}" @endif
            @if($id) id="{{ $id }}" @endif
            @if($disabled) disabled @endif
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'h-9 w-full bg-white dark:bg-[#111827] text-[#111827] dark:text-[#F9FAFB] text-[13px] rounded-[6px] border ' .
                ($hasError ? 'border-[#DC2626] focus:border-[#DC2626] focus:ring-1 focus:ring-[#DC2626]' : 'border-[#E5E7EB] dark:border-[#374151] focus:border-[#F6821F] focus:ring-2 focus:ring-[#F6821F]/20') .
                ' pl-3 pr-8 focus:outline-none transition-colors duration-150 appearance-none cursor-pointer ' .
                ($disabled ? 'bg-[#F7F7F8] dark:bg-[#1F2937] text-[#9CA3AF] cursor-not-allowed' : '')
            ]) }}
        >
            {{ $slot }}
        </select>

        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-[#6B7280]">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </span>
    </div>

    @if($errorMessage)
        <p class="mt-1 text-[12px] text-[#DC2626] leading-tight">{{ $errorMessage }}</p>
    @endif
</div>
