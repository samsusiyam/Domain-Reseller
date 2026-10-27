@extends('layouts.auth', ['title' => 'Set new password'])

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-semibold tracking-tight" style="color: var(--text);">Set new password</h1>
    <p class="text-xs mt-1" style="color: var(--text-muted);">Create a new strong password for your account.</p>
</div>

<form method="POST" action="{{ route('password.store') }}" class="space-y-4">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div>
        <label for="email" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Email address</label>
        <input id="email"
               type="email"
               name="email"
               value="{{ old('email', $request->email) }}"
               required
               autofocus
               class="cf-input h-10 @error('email') border-[#DC2626] @enderror">
        @error('email')
            <p class="text-xs mt-1 font-medium" style="color: var(--danger);">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-xs font-medium mb-1.5" style="color: var(--text);">New password</label>
        <input id="password"
               type="password"
               name="password"
               required
               placeholder="Min 10 characters"
               class="cf-input h-10 @error('password') border-[#DC2626] @enderror">
        @error('password')
            <p class="text-xs mt-1 font-medium" style="color: var(--danger);">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Confirm password</label>
        <input id="password_confirmation"
               type="password"
               name="password_confirmation"
               required
               placeholder="Re-enter new password"
               class="cf-input h-10">
    </div>

    <button type="submit" class="cf-btn cf-btn-primary w-full h-10 font-medium text-sm mt-2">
        Reset password
    </button>
</form>
@endsection
