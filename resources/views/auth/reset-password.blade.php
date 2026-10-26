@extends('layouts.auth', ['title' => 'Set New Password'])

@section('content')
<div class="mb-6 text-center">
    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Set New Password</h2>
    <p class="text-xs text-slate-500 mt-1">Please enter and confirm your new account password</p>
</div>

<form method="POST" action="{{ route('password.store') }}" class="space-y-4">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div>
        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required readonly
            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-500 focus:outline-none">
    </div>

    <div>
        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">New Password</label>
        <input id="password" type="password" name="password" required autofocus
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

    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow-md shadow-indigo-600/20">
        Update Password
    </button>
</form>
@endsection
