@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.resellers.show', $reseller->id) }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Reseller: {{ $reseller->company_name ?: $reseller->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Update wholesale pricing tier, company details, or reset credentials.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('admin.resellers.update', $reseller->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Company / Business Name</label>
                    <input type="text" name="company_name" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('company_name', $reseller->company_name) }}">
                    @error('company_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Contact Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('name', $reseller->name) }}">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Email Address (Login)</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('email', $reseller->email) }}">
                    @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Wholesale Pricing Tier</label>
                    <select name="pricing_tier" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="standard" {{ old('pricing_tier', $reseller->pricing_tier) === 'standard' ? 'selected' : '' }}>Standard (Level 1)</option>
                        <option value="silver" {{ old('pricing_tier', $reseller->pricing_tier) === 'silver' ? 'selected' : '' }}>Silver (Level 2)</option>
                        <option value="gold" {{ old('pricing_tier', $reseller->pricing_tier) === 'gold' ? 'selected' : '' }}>Gold (Level 3)</option>
                        <option value="platinum" {{ old('pricing_tier', $reseller->pricing_tier) === 'platinum' ? 'selected' : '' }}>Platinum (VIP Wholesale)</option>
                    </select>
                    @error('pricing_tier') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Account Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active" {{ old('status', $reseller->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status', $reseller->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="pending" {{ old('status', $reseller->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                    @error('status') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('phone', $reseller->phone) }}">
                    @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.resellers.show', $reseller->id) }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Save Reseller Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
