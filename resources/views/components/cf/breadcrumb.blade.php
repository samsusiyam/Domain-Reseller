@props([
    'items' => [], // array of ['label' => '', 'url' => '']
])

<nav class="flex items-center gap-1.5 text-[12px] text-[#6B7280] dark:text-[#9CA3AF]" aria-label="Breadcrumb">
    @foreach($items as $index => $item)
        @if(!$loop->first)
            <span class="text-[#9CA3AF] dark:text-[#6B7280] select-none">/</span>
        @endif

        @if(!empty($item['url']) && !$loop->last)
            <a href="{{ $item['url'] }}" class="hover:text-[#F6821F] transition-colors duration-150">
                {{ $item['label'] }}
            </a>
        @else
            <span class="font-medium text-[#111827] dark:text-[#F9FAFB] truncate">
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>
