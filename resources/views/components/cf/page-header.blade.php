@props([
    'title',
    'description' => null,
    'breadcrumb' => null,
])

<div {{ $attributes->merge(['class' => 'pb-5 mb-6 border-b border-[#E5E7EB] dark:border-[#374151]']) }}>
    @if($breadcrumb)
        <div class="mb-2">
            {{ $breadcrumb }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[24px] font-semibold text-[#111827] dark:text-[#F9FAFB] tracking-tight leading-tight">
                {{ $title }}
            </h1>
            @if($description)
                <p class="mt-1 text-[13px] text-[#6B7280] dark:text-[#9CA3AF] leading-normal">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if(isset($actions))
            <div class="flex items-center gap-2 shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
