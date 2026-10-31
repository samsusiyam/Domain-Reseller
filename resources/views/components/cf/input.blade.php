@props([
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'disabled' => false,
    'required' => false,
    'error' => null,
    'prefix' => null,
    'suffix' => null,
])

@php
$id = $id ?? $name;
$hasError = $error || ($name && $errors->has($name));
$errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full">
    <div class="relative flex items-center">
        @if($prefix)
            <span class="absolute left-3 text-[#6B7280] dark:text-[#9CA3AF] pointer-events-none text-[13px] flex items-center">
                {!! $prefix !!}
            </span>
        @endif

        <input
            type="{{ $type }}"
            @if($name) name="{{ $name }}" @endif
            @if($id) id="{{ $id }}" @endif
            @if(!is_null($value)) value="{{ $value }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($disabled) disabled @endif
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'h-9 w-full bg-white dark:bg-[#111827] text-[#111827] dark:text-[#F9FAFB] placeholder-[#9CA3AF] text-[13px] rounded-[6px] border ' .
                ($hasError ? 'border-[#DC2626] focus:border-[#DC2626] focus:ring-1 focus:ring-[#DC2626]' : 'border-[#E5E7EB] dark:border-[#374151] focus:border-[#F6821F] focus:ring-2 focus:ring-[#F6821F]/20') .
                ' focus:outline-none transition-colors duration-150 ' .
                ($prefix ? 'pl-9 ' : 'px-3 ') .
                ($suffix ? 'pr-9 ' : 'px-3 ') .
                ($disabled ? 'bg-[#F7F7F8] dark:bg-[#1F2937] text-[#9CA3AF] cursor-not-allowed' : '')
            ]) }}
        />

        @if($suffix)
            <span class="absolute right-3 text-[#6B7280] dark:text-[#9CA3AF] pointer-events-none text-[13px] flex items-center">
                {!! $suffix !!}
            </span>
        @endif
    </div>

    @if($errorMessage)
        <p class="mt-1 text-[12px] text-[#DC2626] leading-tight">{{ $errorMessage }}</p>
    @endif
</div>
