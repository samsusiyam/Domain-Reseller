@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('announcements.index') }}" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1.5 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to All Announcements</span>
        </a>
    </div>

    <article class="glass-card p-8 sm:p-12 rounded-3xl border border-white/10 shadow-2xl">
        <div class="flex items-center gap-3 text-xs text-slate-400 mb-4 font-mono">
            <time datetime="{{ $announcement->published_at?->toDateString() }}">{{ $announcement->published_at?->format('F d, Y') }}</time>
            <span>•</span>
            <span class="capitalize text-indigo-400 font-semibold">{{ $announcement->target_audience === 'all' ? 'General Notice' : 'Client Notice' }}</span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-8 leading-tight">
            {{ $announcement->title }}
        </h1>

        <div class="prose prose-invert prose-indigo max-w-none text-slate-200 leading-relaxed text-sm sm:text-base space-y-4">
            {!! nl2br(e($announcement->content)) !!}
        </div>
    </article>
</div>
@endsection
