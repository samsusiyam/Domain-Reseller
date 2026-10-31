@props([
    'headers' => [], // array of strings or associative ['label' => '', 'align' => 'left'|'right']
])

<div class="w-full border border-[#E5E7EB] dark:border-[#374151] rounded-[8px] overflow-hidden bg-white dark:bg-[#111827]">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-[13px]">
            <thead>
                <tr class="bg-[#F7F7F8] dark:bg-[#1F2937] border-b border-[#E5E7EB] dark:border-[#374151] h-9">
                    @if(!empty($headers))
                        @foreach($headers as $header)
                            @php
                                $isRight = is_array($header) && isset($header['align']) && $header['align'] === 'right';
                                $label = is_array($header) ? $header['label'] : $header;
                            @endphp
                            <th class="px-4 text-[12px] font-medium text-[#6B7280] dark:text-[#9CA3AF] whitespace-nowrap {{ $isRight ? 'text-right' : 'text-left' }}">
                                {{ $label }}
                            </th>
                        @endforeach
                    @else
                        {{ $head ?? '' }}
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E7EB] dark:divide-[#374151] text-[#111827] dark:text-[#F9FAFB]">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($footer))
        <div class="px-4 py-2.5 bg-[#F7F7F8] dark:bg-[#1F2937] border-t border-[#E5E7EB] dark:border-[#374151] text-[12px] text-[#6B7280] dark:text-[#9CA3AF] flex items-center justify-between">
            {{ $footer }}
        </div>
    @endif
</div>
