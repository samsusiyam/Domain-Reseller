@extends('layouts.auth', ['title' => 'Reset password'])

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-semibold tracking-tight" style="color: var(--text);">Forgot password</h1>
    <p class="text-xs mt-1" style="color: var(--text-muted);">Enter your registered email to receive a password reset link.</p>
</div>

@if (session('status'))
    <div class="mb-4 p-3 rounded-[6px] text-xs font-medium"
         style="background-color: var(--success-bg); border: 1px solid var(--success); color: var(--success);">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Email address</label>
        <input id="email"
               type="email"
               name="email"
               value="{{ old('email') }}"
               required
               autofocus
               placeholder="name@company.com"
               class="cf-input h-10 @error('email') border-[#DC2626] @enderror">
        @error('email')
            <p class="text-xs mt-1 font-medium" style="color: var(--danger);">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="cf-btn cf-btn-primary w-full h-10 font-medium text-sm mt-2">
        Send reset link
    </button>
</form>

<div class="mt-5 pt-4 text-center text-xs" style="border-top: 1px solid var(--border); color: var(--text-muted);">
    Remember your password?
    <a href="{{ route('login') }}" class="font-medium hover:underline ml-1" style="color: var(--accent);">Back to sign in</a>
</div>
@endsection
