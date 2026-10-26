@extends('layouts.app')

@section('title', 'Platform Announcements')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Platform Announcements</h1>
            <p class="text-sm text-slate-500 mt-1">Official updates, system maintenance windows, TLD registry promotions, and policy notices.</p>
        </div>
    </div>

    <!-- Announcements Cards -->
    @if($announcements->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 text-center py-16 px-4">
            <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            </div>
            <h3 class="text-base font-semibold text-slate-700">No Announcements</h3>
            <p class="text-sm text-slate-500 mt-1">Everything is running normally. Check back later for news and updates.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($announcements as $item)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
                            <span class="inline-flex items-center gap-1 font-medium text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-full">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $item->published_at ? $item->published_at->format('M d, Y') : $item->created_at->format('M d, Y') }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">
                            <a href="{{ route('client.announcements.show', $item->id) }}" class="hover:text-brand-600 transition">
                                {{ $item->title }}
                            </a>
                        </h3>
                        <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed">
                            {{ Str::limit(strip_tags($item->content), 180) }}
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('client.announcements.show', $item->id) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800 inline-flex items-center gap-1">
                            Read Announcement
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if($announcements->hasPages())
            <div class="pt-4">
                {{ $announcements->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
