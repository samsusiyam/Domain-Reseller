@extends('layouts.app', ['title' => 'Two-Factor Authentication', 'header' => 'Two-Factor Security'])

@section('content')
<div class="max-w-4xl space-y-8">
    <!-- Sub-navigation tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 text-sm font-semibold">
        <a href="{{ route('client.profile.edit') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">Personal Details</a>
        <a href="{{ route('client.two-factor.index') }}" class="px-3.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-600">Two-Factor Authentication</a>
        <a href="{{ route('client.sessions.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">Browser Sessions</a>
    </div>

    @if(session('recoveryCodes'))
        <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl">
            <h4 class="font-bold text-amber-900 text-sm mb-2">Save Your Emergency Recovery Codes!</h4>
            <p class="text-xs text-amber-800 mb-4">
                Store these recovery codes in a secure password manager. If you lose your 2FA device, you can use these one-time codes to sign in.
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-xs bg-white p-4 rounded-xl border border-amber-200">
                @foreach(session('recoveryCodes') as $code)
                    <div class="select-all p-1.5 bg-slate-50 text-center rounded border border-slate-200">{{ $code }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Two-Factor Authentication Status</h3>
                <p class="text-xs text-slate-500 max-w-xl leading-relaxed">
                    Add an extra layer of security to your domain reseller portal account using Google Authenticator, Authy, or 1Password.
                </p>
            </div>
            @if($user->two_factor_confirmed_at)
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Active & Protected
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-600">
                    Disabled
                </span>
            @endif
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100">
            @if($user->two_factor_confirmed_at)
                <p class="text-sm text-slate-600 mb-4">
                    Two-Factor Authentication is currently active on your account. To disable it, enter your current password below.
                </p>
                <form action="{{ route('client.two-factor.disable') }}" method="POST" class="max-w-sm space-y-3">
                    @csrf
                    @method('DELETE')
                    <input type="password" name="password" placeholder="Enter current password to disable" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition">
                        Disable Two-Factor Authentication
                    </button>
                </form>
            @else
                <a href="{{ route('client.two-factor.enable') }}" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow">
                    Enable Two-Factor Authentication &rarr;
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
