@php
    $user = auth()->user();
    $role = $user?->isReseller() ? 'reseller' : 'client';
    $announcement = \App\Models\Announcement::published()->forAudience($role)->latest('published_at')->first();
@endphp

@if($announcement)
    <div x-data="{ show: !localStorage.getItem('dismiss_announcement_{{ $announcement->id }}') }"
         x-show="show"
         x-cloak
         class="mb-5 p-3.5 rounded-lg border border-orange-200 dark:border-orange-900/50 bg-orange-50/70 dark:bg-orange-950/20 text-xs flex items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-2 h-2 rounded-full bg-[#F6821F] shrink-0 animate-pulse"></span>
            <div class="truncate text-slate-800 dark:text-slate-200">
                <strong class="font-semibold text-slate-900 dark:text-white mr-1">{{ $announcement->title }}:</strong>
                <span class="text-slate-600 dark:text-slate-300">{{ Str::limit(strip_tags($announcement->content), 120) }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('announcements.show', $announcement->slug) }}" class="text-[#F6821F] hover:underline font-semibold whitespace-nowrap">
                Read notice &rarr;
            </a>
            <button type="button" @click="show = false; localStorage.setItem('dismiss_announcement_{{ $announcement->id }}', '1')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                ✕
            </button>
        </div>
    </div>
@endif
