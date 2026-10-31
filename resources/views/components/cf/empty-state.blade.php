@props([
    'title' => 'No records found',
    'description' => null,
    'actionText' => null,
    'actionHref' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-10 px-4 border border-dashed border-[#E5E7EB] dark:border-[#374151] rounded-[8px] bg-[#F7F7F8]/50 dark:bg-[#1F2937]/30']) }}>
    <div class="mx-auto w-8 h-8 rounded-[6px] border border-[#E5E7EB] dark:border-[#374151] bg-white dark:bg-[#111827] text-[#6B7280] dark:text-[#9CA3AF] flex items-center justify-center mb-3">
        @if($icon)
            {!! $icon !!}
        @else
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        @endif
    </div>

    <h4 class="text-[14px] font-semibold text-[#111827] dark:text-[#F9FAFB]">
        {{ $title }}
    </h4>

    @if($description)
        <p class="mt-1 text-[13px] text-[#6B7280] dark:text-[#9CA3AF] max-w-sm mx-auto leading-normal">
            {{ $description }}
        </p>
    @endif

    @if($actionText && $actionHref)
        <div class="mt-4">
            <x-cf.button variant="secondary" size="sm" :href="$actionHref">
                {{ $actionText }}
            </x-cf.button>
        </div>
    @endif
</div>
