@extends('layouts.app')

@section('title', $announcement->title . ' - Announcements')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Navigation Back -->
    <div>
        <a href="{{ route('client.announcements.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Announcements
        </a>
    </div>

    <!-- Announcement Content Article -->
    <article class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12">
        <header class="border-b border-slate-200 pb-6 mb-8">
            <div class="flex items-center gap-2 text-xs text-brand-600 font-semibold mb-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-brand-50 border border-brand-100">
                    Official Announcement
                </span>
                <span>&bull;</span>
                <time datetime="{{ $announcement->published_at?->toIso8601String() }}">
                    {{ $announcement->published_at ? $announcement->published_at->format('F d, Y') : $announcement->created_at->format('F d, Y') }}
                </time>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                {{ $announcement->title }}
            </h1>
        </header>

        <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed space-y-4">
            {!! nl2br(e($announcement->content)) !!}
        </div>

        <footer class="mt-12 pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <span>Posted by Platform Operations</span>
            <a href="{{ route('client.announcements.index') }}" class="text-brand-600 hover:text-brand-800 font-semibold">
                &larr; View all updates
            </a>
        </footer>
    </article>
</div>
@endsection
