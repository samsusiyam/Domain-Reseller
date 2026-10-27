@extends('layouts.admin', ['title' => 'Edit Client', 'header' => 'Edit Client'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.clients.show', $client->id) }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Client: {{ $client->name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Update user profile specifications, physical address, and status</p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs">
            <ul class="list-disc list-inside space-y-1 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('admin.clients.update', $client->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="cf-input w-full" value="{{ old('name', $client->name) }}">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="cf-input w-full" value="{{ old('email', $client->email) }}">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password (Leave blank to keep unchanged)</label>
                    <input type="password" name="password" class="cf-input w-full" placeholder="••••••••">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Company / Organization</label>
                    <input type="text" name="company_name" class="cf-input w-full" value="{{ old('company_name', $client->company_name) }}">
                    @error('company_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" class="cf-input w-full" value="{{ old('phone', $client->phone) }}">
                    @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Account Status <span class="text-red-500">*</span></label>
                    <select name="status" class="cf-input w-full font-semibold">
                        <option value="active" {{ old('status', $client->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status', $client->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="pending" {{ old('status', $client->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                    @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Street Address</label>
                    <input type="text" name="address_1" class="cf-input w-full" value="{{ old('address_1', $client->address_1) }}">
                    @error('address_1') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">City</label>
                    <input type="text" name="city" class="cf-input w-full" value="{{ old('city', $client->city) }}">
                    @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Country (ISO 2-letter)</label>
                    <input type="text" name="country" maxlength="2" class="cf-input w-full uppercase font-mono" value="{{ old('country', $client->country) }}">
                    @error('country') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.clients.show', $client->id) }}" class="cf-btn cf-btn-secondary text-xs">Cancel</a>
                <button type="submit" class="cf-btn cf-btn-primary text-xs shadow-xs">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
