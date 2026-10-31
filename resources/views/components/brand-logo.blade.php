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

<div class="inline-flex items-center gap-2 shrink-0 select-none">
    @if(!empty($logoLight) && !empty($logoDark))
        <img src="{{ asset($logoLight) }}"
             alt="{{ $appName }}"
             class="{{ $class }} brand-logo-light object-contain"
             style="max-height: 36px; width: auto;">
        <img src="{{ asset($logoDark) }}"
             alt="{{ $appName }}"
             class="{{ $class }} brand-logo-dark object-contain"
             style="max-height: 36px; width: auto;">
    @elseif(!empty($logoLight))
        <img src="{{ asset($logoLight) }}"
             alt="{{ $appName }}"
             class="{{ $class }} object-contain"
             style="max-height: 36px; width: auto;">
    @elseif(!empty($logoDark))
        <img src="{{ asset($logoDark) }}"
             alt="{{ $appName }}"
             class="{{ $class }} object-contain"
             style="max-height: 36px; width: auto;">
    @else
        <!-- Crisp Fallback Icon + Text -->
        <div class="w-7 h-7 rounded-lg bg-[#F6821F] flex items-center justify-center text-white shrink-0 font-bold text-xs shadow-xs">
            H
        </div>
        <span class="font-bold text-sm tracking-tight text-slate-900 dark:text-white leading-tight">HOST NIBO</span>
    @endif

    @if($showText)
        <span class="{{ $textClass }} text-slate-900 dark:text-white truncate">
            {{ $appName }}
        </span>
    @endif
</div>
