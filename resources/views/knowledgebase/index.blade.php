@extends('layouts.guest')

@section('content')
<div class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">Knowledgebase & Guides</h1>
            <p class="mt-2 text-sm text-slate-600">
                Browse detailed tutorials and guides on managing DNS, connecting WHMCS, API integration, and billing.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($categories as $cat)
                <a href="{{ route('knowledgebase.show', $cat['slug']) }}" class="p-8 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-200 transition group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 rounded-full text-slate-600">
                                {{ $cat['articles_count'] }} articles
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition mb-2">
                            {{ $cat['title'] }}
                        </h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            {{ $cat['description'] }}
                        </p>
                    </div>
                    <div class="mt-6 flex items-center text-xs font-bold text-indigo-600 group-hover:translate-x-1 transition">
                        Explore Category &rarr;
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
