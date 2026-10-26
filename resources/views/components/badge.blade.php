@props(['color' => 'gray', 'size' => 'md'])

@php
$colorClasses = match($color) {
    'green', 'success', 'active', 'paid' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-400',
    'red', 'danger', 'failed', 'cancelled', 'suspended' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20 dark:bg-rose-950/50 dark:text-rose-400',
    'yellow', 'amber', 'warning', 'pending' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-950/50 dark:text-amber-400',
    'blue', 'info', 'processing' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-950/50 dark:text-blue-400',
    'purple', 'reseller', 'platinum' => 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20 dark:bg-purple-950/50 dark:text-purple-400',
    default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-600/10 dark:bg-slate-800 dark:text-slate-300',
};

$sizeClasses = match($size) {
    'sm' => 'px-2 py-0.5 text-xs',
    'lg' => 'px-3 py-1 text-sm font-semibold',
    default => 'px-2.5 py-0.5 text-xs font-medium',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full {$sizeClasses} {$colorClasses} uppercase tracking-wider font-semibold"]) }}>
    {{ $slot }}
</span>
