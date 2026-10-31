@props([
    'tabs' => [], // array of ['id' => '', 'label' => '', 'href' => '', 'active' => bool, 'count' => null]
])

<div class="border-b border-[#E5E7EB] dark:border-[#374151]">
    <nav class="flex gap-1 -mb-px overflow-x-auto" aria-label="Tabs">
        @if(!empty($tabs))
            @foreach($tabs as $tab)
                @php
                    $isActive = $tab['active'] ?? false;
                @endphp
                <a
                    href="{{ $tab['href'] ?? '#' }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-medium border-b-2 transition-colors duration-150 whitespace-nowrap {{ $isActive ? 'border-[#F6821F] text-[#111827] dark:text-[#F9FAFB] font-semibold' : 'border-transparent text-[#6B7280] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-[#F9FAFB] hover:border-[#D1D5DB]' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    @if(isset($tab['count']) && $tab['count'] !== null)
                        <span class="text-[11px] font-mono tabular-nums px-1.5 py-0.2 rounded bg-[#F7F7F8] dark:bg-[#1F2937] text-[#6B7280] dark:text-[#9CA3AF] border border-[#E5E7EB] dark:border-[#374151]">
                            {{ $tab['count'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </nav>
</div>
