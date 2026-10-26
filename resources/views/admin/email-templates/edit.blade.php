@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.email-templates.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Template: {{ $template->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Customize email notification wording and variable placeholders.</p>
        </div>
    </div>

    <!-- Available Variables Cheat Sheet -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4">
        <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider block mb-2">Available Merge Variables:</span>
        <div class="flex flex-wrap gap-2 text-xs font-mono">
            <span class="bg-white text-indigo-700 px-2 py-1 rounded border border-indigo-200">@{{client_name}}</span>
            <span class="bg-white text-indigo-700 px-2 py-1 rounded border border-indigo-200">@{{domain_name}}</span>
            <span class="bg-white text-indigo-700 px-2 py-1 rounded border border-indigo-200">@{{expiry_date}}</span>
            <span class="bg-white text-indigo-700 px-2 py-1 rounded border border-indigo-200">@{{invoice_id}}</span>
            <span class="bg-white text-indigo-700 px-2 py-1 rounded border border-indigo-200">@{{total_amount}}</span>
            <span class="bg-white text-indigo-700 px-2 py-1 rounded border border-indigo-200">@{{company_name}}</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('admin.email-templates.update', $template->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Email Subject Line</label>
                <input type="text" name="subject" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold"
                    value="{{ old('subject', $template->subject) }}">
                @error('subject') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">HTML Message Body</label>
                <textarea name="body_html" rows="12" required class="w-full px-4 py-3 font-mono text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('body_html', $template->body_html) }}</textarea>
                @error('body_html') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-800">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                    Template Active (System sends automated notifications)
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.email-templates.index') }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Save Template
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
