@props([
    'class' => 'h-7 w-auto',
    'showText' => false,
    'textClass' => 'font-bold text-[15px] tracking-tight',
    'fallbackIconClass' => 'w-4 h-4'
])

@php
    $logoLight = \App\Models\SystemSetting::get('logo_light');
    $logoDark = \App\Models\SystemSetting::get('logo_dark');
    $appName = \App\Models\SystemSetting::get('company_name', config('app.name', 'HostNibo Domain Hub'));
@endphp

<div class="inline-flex items-center gap-2.5">
    @if(!empty($logoLight) || !empty($logoDark))
        @if(!empty($logoLight))
            <img src="{{ asset($logoLight) }}"
                 alt="{{ $appName }}"
                 class="{{ $class }} {{ !empty($logoDark) ? 'dark:hidden' : '' }} object-contain">
        @endif

        @if(!empty($logoDark))
            <img src="{{ asset($logoDark) }}"
                 alt="{{ $appName }}"
                 class="{{ $class }} {{ !empty($logoLight) ? 'hidden dark:block' : '' }} object-contain">
        @elseif(!empty($logoLight))
            <img src="{{ asset($logoLight) }}"
                 alt="{{ $appName }}"
                 class="{{ $class }} object-contain">
        @endif
    @else
        <!-- Flame Accent Mark Fallback -->
        <div class="w-7 h-7 rounded-[6px] bg-[#F6821F] flex items-center justify-center text-white shrink-0 shadow-none">
            <svg class="{{ $fallbackIconClass }}" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
            </svg>
        </div>
    @endif

    @if($showText)
        <span class="{{ $textClass }} text-slate-900 dark:text-white truncate">
            {{ $appName }}
        </span>
    @endif
</div>
