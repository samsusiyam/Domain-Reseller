@extends('layouts.guest')

@section('content')
<div class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('announcements.index') }}" class="text-xs font-bold text-indigo-600 hover:underline mb-4 inline-flex items-center gap-1">
            &larr; Back to Announcements
        </a>

        <div class="flex items-center gap-3 text-xs text-slate-400 mb-3">
            <time datetime="{{ $announcement->published_at?->toDateString() }}">{{ $announcement->published_at?->format('F d, Y') }}</time>
            <span>•</span>
            <span class="capitalize text-indigo-600 font-semibold">{{ $announcement->target_audience }}</span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-8">
            {{ $announcement->title }}
        </h1>

        <div class="prose prose-slate max-w-none text-base leading-relaxed text-slate-700 border-t border-slate-100 pt-8">
            {!! nl2br(e($announcement->content)) !!}
        </div>
    </div>
</div>
@endsection
