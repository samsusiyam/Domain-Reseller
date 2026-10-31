@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Platform Announcements</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Broadcast official registry notices, maintenance schedules, and wholesale announcements.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Publish New Announcement Form -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Create Announcement</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Broadcast a notice to users or resellers.</p>

                <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Title</label>
                        <input type="text" name="title" required placeholder="Maintenance Window or Promotion"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('title') }}">
                        @error('title') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Target Audience</label>
                        <select name="target_audience" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">All (Clients, Resellers & Public)</option>
                            <option value="clients">Clients Only</option>
                            <option value="resellers">Resellers Only</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Announcement Body</label>
                        <textarea name="content" rows="6" required placeholder="Full announcement text..."
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('content') }}</textarea>
                        @error('content') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800 dark:text-white">
                            <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-indigo-600">
                            Publish Immediately
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                        Broadcast Announcement
                    </button>
                </form>
            </div>
        </div>

        <!-- Announcements Table -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">Published Announcements</h3>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 dark:text-slate-300 rounded-full">
                        {{ $announcements->total() }} Total
                    </span>
                </div>

                @if($announcements->isEmpty())
                    <div class="text-center py-16 px-4 text-slate-400 text-sm">
                        No announcements posted.
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($announcements as $ann)
                            <div class="p-6 hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-slate-900 dark:text-white text-base">{{ $ann->title }}</h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                                {{ $ann->target_audience === 'resellers' ? 'bg-purple-100 text-purple-800' :
                                                   ($ann->target_audience === 'clients' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700 dark:text-slate-300') }}">
                                                {{ $ann->target_audience }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-400">
                                            Published {{ $ann->published_at ? $ann->published_at->format('M d, Y') : $ann->created_at->format('M d, Y') }}
                                        </p>
                                        <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2 pt-2">
                                            {{ Str::limit(strip_tags($ann->content), 200) }}
                                        </p>
                                    </div>

                                    <form action="{{ route('admin.announcements.destroy', $ann->id) }}" method="POST" onsubmit="return confirm('Delete this announcement?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-bold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($announcements->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $announcements->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
