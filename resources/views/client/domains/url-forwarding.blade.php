@extends('layouts.app')

@section('title', 'URL Redirection - ' . $domain->domain_name)

@section('content')
<div class="space-y-6">
    <x-domain-tabs :domain="$domain" active="url-forwarding" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ redirectType: '301' }">
        <!-- Add URL Forward Form -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 dark:border-slate-800 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Add URL Redirect</h3>
                <form action="{{ route('client.domains.url-forwarding.store', $domain->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Source Subdomain</label>
                        <div class="flex rounded-lg shadow-sm border border-slate-300 overflow-hidden focus-within:ring-2 focus-within:ring-brand-500 focus-within:border-brand-500">
                            <input type="text" name="source_subdomain" placeholder="@"
                                class="flex-1 px-3 py-2 text-sm text-slate-800 dark:text-white border-none focus:outline-none"
                                value="{{ old('source_subdomain', '@') }}">
                            <span class="inline-flex items-center px-3 bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-sm border-l border-slate-300">
                                .{{ $domain->domain_name }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Use "@" for root domain, or enter subdomain (e.g. "go", "shop").</p>
                        @error('source_subdomain') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Destination URL</label>
                        <input type="url" name="destination_url" placeholder="https://example.com/target" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            value="{{ old('destination_url') }}">
                        <p class="text-xs text-slate-500 mt-1">Must include protocol (http:// or https://).</p>
                        @error('destination_url') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Redirect Type</label>
                        <select name="redirect_type" x-model="redirectType"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <option value="301">301 - Permanent Redirect (SEO Recommended)</option>
                            <option value="302">302 - Temporary Redirect</option>
                            <option value="masked">Masked / Cloaked (URL Remains in Address Bar)</option>
                        </select>
                        @error('redirect_type') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="redirectType === 'masked'" x-cloak>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Masked Page Title</label>
                        <input type="text" name="page_title" placeholder="My Website"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            value="{{ old('page_title') }}">
                        <p class="text-xs text-slate-500 mt-1">Browser tab title when visitors view the cloaked site.</p>
                        @error('page_title') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        Save Redirect Rule
                    </button>
                </form>
            </div>
        </div>

        <!-- Redirections List -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Active URL Redirects</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Visitors accessing these hosts will be routed accordingly.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full">
                        {{ $forwards->count() }} Configured
                    </span>
                </div>

                @if($forwards->isEmpty())
                    <div class="text-center py-12 px-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </div>
                        <h4 class="text-base font-semibold text-slate-700">No URL Redirects Configured</h4>
                        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Redirect root or subdomains directly to social channels, landing pages, or another website.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3.5">Source Host</th>
                                    <th class="px-6 py-3.5">Type</th>
                                    <th class="px-6 py-3.5">Target Destination</th>
                                    <th class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($forwards as $forward)
                                    <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                        <td class="px-6 py-4 font-mono font-medium text-slate-800 dark:text-white">
                                            {{ $forward->source_subdomain === '@' ? $domain->domain_name : $forward->source_subdomain . '.' . $domain->domain_name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($forward->redirect_type === '301')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">301 Permanent</span>
                                            @elseif($forward->redirect_type === '302')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:text-blue-300">302 Temporary</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Masked</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs text-brand-600 truncate max-w-xs">
                                            <a href="{{ $forward->destination_url }}" target="_blank" rel="noopener" class="hover:underline flex items-center gap-1">
                                                {{ $forward->destination_url }}
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('client.domains.url-forwarding.destroy', [$domain->id, $forward->id]) }}" method="POST" onsubmit="return confirm('Remove this redirect rule?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-semibold inline-flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
