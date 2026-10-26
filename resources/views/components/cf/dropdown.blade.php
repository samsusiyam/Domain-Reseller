@props([
    'align' => 'right', // right, left
    'width' => 'w-48',
])

@php
$alignmentClasses = match($align) {
    'left' => 'left-0 origin-top-left',
    default => 'right-0 origin-top-right',
};
@endphp

<div class="relative inline-block text-left" x-data="{ open: false }" @click.away="open = false">
    <div @click="open = !open">
        @if(isset($trigger))
            {{ $trigger }}
        @else
            <button
                type="button"
                class="h-8 w-8 inline-flex items-center justify-center rounded-[6px] border border-transparent hover:border-[#E5E7EB] dark:hover:border-[#374151] hover:bg-[#F7F7F8] dark:hover:bg-[#1F2937] text-[#6B7280] hover:text-[#111827] dark:hover:text-[#F9FAFB] transition-colors duration-150"
                aria-label="Actions"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="19" cy="12" r="1"></circle>
                    <circle cx="5" cy="12" r="1"></circle>
                </svg>
            </button>
        @endif
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-1 {{ $width }} {{ $alignmentClasses }} rounded-[6px] bg-white dark:bg-[#111827] border border-[#E5E7EB] dark:border-[#374151] shadow-lg py-1 focus:outline-none"
    >
        {{ $slot }}
    </div>
</div>
