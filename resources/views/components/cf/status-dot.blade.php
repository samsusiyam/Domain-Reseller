@props([
    'status' => 'neutral', // success, warning, danger, info, neutral
    'label' => null,
])

@php
$dotColor = match(strtolower($status)) {
    'success', 'active', 'paid', 'completed', 'verified' => 'bg-[#16A34A]',
    'warning', 'pending', 'unpaid', 'processing' => 'bg-[#D97706]',
    'danger', 'error', 'failed', 'cancelled', 'suspended', 'expired' => 'bg-[#DC2626]',
    'info', 'processing', 'transferring' => 'bg-[#2563EB]',
    default => 'bg-[#9CA3AF]',
};
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-[12px] font-medium text-[#111827] dark:text-[#F9FAFB] select-none']) }}>
    <span class="w-2 h-2 rounded-full shrink-0 {{ $dotColor }}" aria-hidden="true"></span>
    <span>{{ $label ?? $slot }}</span>
</span>
