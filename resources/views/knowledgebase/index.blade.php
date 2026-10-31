@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
            Self-Service Documentation
        </span>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mt-4">Knowledgebase & Guides</h1>
        <p class="mt-3 text-sm text-slate-400">
            Browse step-by-step tutorials on DNS management, WHMCS registrar module setup, REST API authentication, and billing.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($categories as $cat)
            <a href="{{ route('knowledgebase.show', $cat['slug']) }}"
                class="p-8 rounded-3xl glass-card border border-white/10 hover:border-indigo-500/40 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                        <span class="text-xs font-mono font-bold px-2.5 py-1 bg-white/5 border border-white/10 rounded-full text-indigo-300">
                            {{ $cat['articles_count'] }} articles
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-white group-hover:text-indigo-400 transition mb-2">
                        {{ $cat['title'] }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        {{ $cat['description'] }}
                    </p>
                </div>
                <div class="mt-8 flex items-center text-xs font-bold text-indigo-400 group-hover:text-indigo-300 group-hover:translate-x-1 transition gap-1.5">
                    <span>Explore Category</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
