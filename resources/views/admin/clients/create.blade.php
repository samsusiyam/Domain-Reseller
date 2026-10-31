@extends('layouts.admin', ['title' => 'Create Client Account', 'header' => 'New Client'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.clients.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Create Client Account</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manually register a direct retail user profile</p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('admin.clients.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="cf-input w-full" value="{{ old('name') }}">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="cf-input w-full" value="{{ old('email') }}">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required class="cf-input w-full">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Company / Organization</label>
                    <input type="text" name="company_name" class="cf-input w-full" value="{{ old('company_name') }}">
                    @error('company_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" class="cf-input w-full" value="{{ old('phone') }}">
                    @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Country (ISO 2-letter)</label>
                    <input type="text" name="country" value="{{ old('country', 'BD') }}" maxlength="2" class="cf-input w-full uppercase font-mono">
                    @error('country') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Street Address</label>
                    <input type="text" name="address_1" class="cf-input w-full" value="{{ old('address_1') }}">
                    @error('address_1') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">City</label>
                    <input type="text" name="city" class="cf-input w-full" value="{{ old('city') }}">
                    @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">State / Province</label>
                    <input type="text" name="state" class="cf-input w-full" value="{{ old('state') }}">
                    @error('state') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.clients.index') }}" class="cf-btn cf-btn-secondary text-xs">Cancel</a>
                <button type="submit" class="cf-btn cf-btn-primary text-xs">
                    Create Client Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
