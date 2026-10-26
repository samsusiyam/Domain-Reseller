@extends('layouts.guest')

@section('content')
<div class="py-16 bg-slate-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">Platform Announcements</h1>
            <p class="mt-2 text-sm text-slate-600">
                Latest updates, upstream registry notices, and maintenance schedules.
            </p>
        </div>

        <div class="space-y-6">
            @forelse($announcements as $item)
                <article class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm hover:shadow transition">
                    <div class="flex items-center gap-3 text-xs text-slate-400 mb-2">
                        <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('M d, Y') }}</time>
                        <span>•</span>
                        <span class="capitalize text-indigo-600 font-semibold">{{ $item->target_audience === 'all' ? 'General Notice' : 'Client Notice' }}</span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3 hover:text-indigo-600 transition">
                        <a href="{{ route('announcements.show', $item->slug) }}">{{ $item->title }}</a>
                    </h2>
                    <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed">
                        {{ strip_tags($item->content) }}
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('announcements.show', $item->slug) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                            Read Full Article &rarr;
                        </a>
                    </div>
                </article>
            @empty
                <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 text-slate-500">
                    No announcements published at this time.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection
