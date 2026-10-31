@extends('layouts.app', ['title' => 'Profile Settings', 'header' => 'Profile & Security'])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Sub-navigation tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 text-xs font-semibold overflow-x-auto">
        <a href="{{ route('client.profile.edit') }}" class="px-3.5 py-1.5 rounded-lg bg-[#F6821F]/10 text-[#F6821F]">Personal Details</a>
        <a href="{{ route('client.contacts.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">Contact Profiles</a>
        <a href="{{ route('client.two-factor.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">Two-Factor Authentication</a>
        <a href="{{ route('client.sessions.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">Browser Sessions</a>
    </div>

    <!-- Personal Profile Form -->
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">Billing & Personal Identity</h3>
        <p class="text-xs text-slate-500 mb-6">This default profile is used for invoices and WHOIS domain registrations.</p>

        <form action="{{ route('client.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address (Read-only)</label>
                    <input type="email" value="{{ $user->email }}" readonly class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Company / Organization</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Street Address</label>
                <input type="text" name="address_1" value="{{ old('address_1', $user->address_1 ?? $user->address_line_1) }}" class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">State / Province</label>
                    <input type="text" name="state" value="{{ old('state', $user->state) }}" class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Country (ISO 2)</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" maxlength="2" placeholder="BD" class="w-full px-3.5 py-2 text-xs font-mono uppercase border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="h-9 px-5 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg shadow-sm transition cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Passkeys (WebAuthn / Biometrics) Section -->
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm" x-data="{
        registering: false,
        passkeyName: '',
        errorMessage: '',
        showPrompt: false,

        async registerPasskey() {
            if (!window.PublicKeyCredential) {
                this.errorMessage = 'Passkeys (WebAuthn) are not supported on this browser.';
                return;
            }
            this.registering = true;
            this.errorMessage = '';

            try {
                let optRes = await fetch('{{ route('client.passkeys.register.options') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                let options = await optRes.json();

                options.challenge = Uint8Array.from(atob(options.challenge), c => c.charCodeAt(0));
                options.user.id = Uint8Array.from(atob(options.user.id), c => c.charCodeAt(0));

                let credential = await navigator.credentials.create({ publicKey: options });
                let rawId = btoa(String.fromCharCode(...new Uint8Array(credential.rawId)));
                let name = this.passkeyName.trim() || 'My Passkey Device';

                let saveRes = await fetch('{{ route('client.passkeys.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        credential_id: rawId,
                        device_type: 'platform'
                    })
                });
                let resData = await saveRes.json();
                if (resData.success) {
                    window.location.reload();
                } else {
                    this.errorMessage = resData.message || 'Failed to save passkey.';
                }
            } catch(e) {
                if (e.name === 'NotAllowedError') {
                    this.errorMessage = 'Passkey setup cancelled.';
                } else {
                    this.errorMessage = e.message || 'Passkey registration error.';
                }
            } finally {
                this.registering = false;
            }
        }
    }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Passkeys & Biometric Security</h3>
                <p class="text-xs text-slate-500 mt-1">Sign in instantly using Touch ID, Face ID, Windows Hello, or hardware security keys without passwords.</p>
            </div>
            <button type="button" @click="showPrompt = !showPrompt" class="h-8 px-3.5 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg shadow-sm flex items-center gap-1.5 transition self-start sm:self-auto cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Add passkey</span>
            </button>
        </div>

        <div x-show="errorMessage" x-cloak class="mb-4 p-3 rounded-lg text-xs font-medium text-red-600 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800" x-text="errorMessage"></div>

        <!-- Add Passkey Input Dialog -->
        <div x-show="showPrompt" x-cloak class="mb-6 p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 space-y-3">
            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200">Register new passkey device</h4>
            <div class="flex flex-col sm:flex-row gap-2 max-w-md">
                <input type="text" x-model="passkeyName" placeholder="Device name (e.g. MacBook Touch ID, iPhone Face ID)"
                    class="flex-1 px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                <button type="button" @click="registerPasskey()" :disabled="registering" class="h-9 px-4 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg transition shrink-0 cursor-pointer">
                    <span x-text="registering ? 'Authorizing...' : 'Scan / Create'"></span>
                </button>
            </div>
            <p class="text-[11px] text-slate-500">Your browser will prompt for your device fingerprint, facial scan, or security key.</p>
        </div>

        @php
            $passkeys = $user->passkeys()->latest()->get();
        @endphp

        @if($passkeys->isEmpty())
            <div class="p-4 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-500">
                No passkeys registered yet. Click "Add passkey" to link your device.
            </div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($passkeys as $pk)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-[#F6821F]/10 text-[#F6821F] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $pk->name }}</p>
                                <p class="text-[11px] text-slate-500">
                                    Added {{ $pk->created_at->format('M d, Y') }} • Last used {{ $pk->last_used_at ? $pk->last_used_at->diffForHumans() : 'Never' }}
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('client.passkeys.destroy', $pk->id) }}" method="POST" onsubmit="return confirm('Remove this passkey?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium cursor-pointer">
                                Remove
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Change Password Form -->
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">Change Account Password</h3>
        <p class="text-xs text-slate-500 mb-6">Ensure your account is protected with a long, unique password.</p>

        <form action="{{ route('client.profile.password') }}" method="POST" class="space-y-4 max-w-lg">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Current Password</label>
                <input type="password" name="current_password" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none @error('current_password') border-red-500 @enderror">
                @error('current_password')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">New Password</label>
                <input type="password" name="password" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            </div>

            <div class="pt-2">
                <button type="submit" class="h-9 px-5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition cursor-pointer">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
