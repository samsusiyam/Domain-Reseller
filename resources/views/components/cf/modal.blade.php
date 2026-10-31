@props([
    'id' => 'modal',
    'title' => null,
    'size' => 'md', // sm (400px), md (520px), lg (680px)
])

@php
$maxWidth = match($size) {
    'sm' => 'sm:max-w-md',
    'lg' => 'sm:max-w-2xl',
    default => 'sm:max-w-lg',
};
@endphp

<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    id="{{ $id }}"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="{{ $id }}-title"
    role="dialog"
    aria-modal="true"
>
    <!-- Dark Backdrop (flat, simple opacity fade) -->
    <div
        x-show="open"
        x-transition:enter="transition-opacity duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 dark:bg-black/70"
        @click="open = false"
    ></div>

    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div
            x-show="open"
            x-transition:enter="transition-all duration-150"
            x-transition:enter-start="opacity-0 scale-98"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition-all duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-98"
            class="relative transform overflow-hidden rounded-[8px] bg-white dark:bg-[#111827] text-left border border-[#E5E7EB] dark:border-[#374151] shadow-xl transition-all sm:my-8 w-full {{ $maxWidth }}"
        >
            @if($title)
                <div class="flex items-center justify-between px-4 py-3 bg-[#F7F7F8] dark:bg-[#1F2937] border-b border-[#E5E7EB] dark:border-[#374151]">
                    <h3 class="text-[14px] font-semibold text-[#111827] dark:text-[#F9FAFB]" id="{{ $id }}-title">
                        {{ $title }}
                    </h3>
                    <button
                        type="button"
                        @click="open = false"
                        class="p-1 rounded-[4px] text-[#6B7280] hover:text-[#111827] dark:text-[#9CA3AF] dark:hover:text-[#F9FAFB] hover:bg-[#E5E7EB] dark:hover:bg-[#374151] transition-colors"
                        aria-label="Close"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="px-5 py-4 text-[13px] text-[#111827] dark:text-[#F9FAFB]">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="px-4 py-3 bg-[#F7F7F8] dark:bg-[#1F2937] border-t border-[#E5E7EB] dark:border-[#374151] flex items-center justify-end gap-2">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
