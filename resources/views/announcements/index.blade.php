@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
            Official Broadcasts
        </span>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mt-4">Platform Announcements</h1>
        <p class="mt-2 text-sm text-slate-400">
            Latest platform updates, upstream registry notices, policy changes, and maintenance schedules.
        </p>
    </div>

    <div class="space-y-6">
        @forelse($announcements as $item)
            <article class="glass-card p-6 sm:p-8 rounded-3xl border border-white/10 hover:border-indigo-500/40 transition">
                <div class="flex items-center gap-3 text-xs text-slate-400 mb-2 font-mono">
                    <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('M d, Y') }}</time>
                    <span>•</span>
                    <span class="capitalize text-indigo-400 font-semibold">{{ $item->target_audience === 'all' ? 'General Notice' : 'Client Notice' }}</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white mb-3 hover:text-indigo-400 transition">
                    <a href="{{ route('announcements.show', $item->slug) }}">{{ $item->title }}</a>
                </h2>
                <p class="text-sm text-slate-300 line-clamp-3 leading-relaxed">
                    {{ strip_tags($item->content) }}
                </p>
                <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                    <a href="{{ route('announcements.show', $item->slug) }}" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1.5 transition">
                        <span>Read Full Announcement</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="p-16 text-center glass-card rounded-3xl border border-white/10 text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto mb-4 font-bold">
                    📣
                </div>
                <h3 class="text-base font-bold text-white">No Announcements Published</h3>
                <p class="text-xs text-slate-400 mt-1">All registry and payment systems are operational. Check back soon for news and promotions.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $announcements->links() }}
    </div>
</div>
@endsection
