@extends('layouts.auth', ['title' => 'Create Account'])

@section('content')
<div class="mb-6 text-center">
    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Create an Account</h2>
    <p class="text-xs text-slate-500 mt-1">Join as a direct client or wholesale domain reseller</p>
</div>

<form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ role: 'client' }">
    @csrf

    <!-- Role Selection Tabs -->
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Account Type</label>
        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
            <button type="button" @click="role = 'client'"
                :class="role === 'client' ? 'bg-white shadow-sm text-indigo-600 font-bold' : 'text-slate-600 font-medium'"
                class="py-2 text-xs rounded-lg transition text-center">
                Direct Client
            </button>
            <button type="button" @click="role = 'reseller'"
                :class="role === 'reseller' ? 'bg-white shadow-sm text-indigo-600 font-bold' : 'text-slate-600 font-medium'"
                class="py-2 text-xs rounded-lg transition text-center">
                Domain Reseller (API)
            </button>
        </div>
        <input type="hidden" name="role" :value="role">
    </div>

    <div>
        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('name') border-rose-500 @enderror">
        @error('name')
            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="role === 'reseller'" x-cloak>
        <label for="company_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Company / Brand Name</label>
        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
    </div>

    <div>
        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('email') border-rose-500 @enderror">
        @error('email')
            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="+8801700000000"
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
    </div>

    <div>
        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
        <input id="password" type="password" name="password" required
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('password') border-rose-500 @enderror">
        @error('password')
            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
    </div>

    <div class="flex items-start gap-2 pt-2">
        <input id="terms" type="checkbox" name="terms" required class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
        <label for="terms" class="text-xs text-slate-600 leading-tight">
            I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-indigo-600 underline">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank" class="text-indigo-600 underline">Privacy Policy</a>.
        </label>
    </div>

    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow-md shadow-indigo-600/20">
        Create Account
    </button>
</form>

<div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
    Already have an account?
    <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:underline">Sign in</a>
</div>
@endsection
