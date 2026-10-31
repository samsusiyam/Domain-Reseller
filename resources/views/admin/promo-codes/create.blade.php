@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.promo-codes.index') }}" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Create Promotional Code</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Define coupon code, discount amount, usage quotas, and TLD restrictions.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <form action="{{ route('admin.promo-codes.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Coupon Code (Uppercase)</label>
                    <input type="text" name="code" placeholder="PROMO2026" required class="w-full px-3 py-2 font-mono text-sm uppercase border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('code') }}">
                    @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <input type="text" name="description" placeholder="Summer registration special" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('description') }}">
                    @error('description') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Discount Type</label>
                    <select name="discount_type" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage Discount (%)</option>
                        <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount Off ($ USD)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Discount Value</label>
                    <input type="number" step="0.01" min="0.01" name="discount_value" required placeholder="10.00" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('discount_value') }}">
                    @error('discount_value') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Global Max Uses (Blank for unlimited)</label>
                    <input type="number" min="1" name="max_uses" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('max_uses') }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Limit Per Customer</label>
                    <input type="number" min="1" name="user_usage_limit" value="{{ old('user_usage_limit', 1) }}" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Valid From Date</label>
                    <input type="date" name="valid_from" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('valid_from') }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Valid To / Expiry Date</label>
                    <input type="date" name="valid_to" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('valid_to') }}">
                </div>
            </div>

            <!-- Applicable TLDs -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Applicable TLD Extensions (Leave unselected for all TLDs)</label>
                <div class="flex flex-wrap gap-2 max-h-36 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800">
                    @foreach($tlds as $tldStr)
                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg text-xs font-mono cursor-pointer hover:border-indigo-400">
                            <input type="checkbox" name="applicable_tlds[]" value="{{ $tldStr }}" class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-indigo-600">
                            {{ $tldStr }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.promo-codes.index') }}" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-800/50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Create Promo Code
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
