@extends('layouts.reseller', ['header' => 'Profile & Security'])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold overflow-x-auto pb-2">
        <a href="{{ route('reseller.profile.edit') }}" class="px-3.5 py-1.5 rounded-lg bg-[#F6821F]/10 text-[#F6821F] font-bold">Profile, Password & Passkeys</a>
        <a href="{{ route('reseller.two-factor.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Two-Factor Authentication</a>
        <a href="{{ route('reseller.sessions.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Active Sessions</a>
    </div>

    <!-- Personal Profile Details -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-xs">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Reseller Account Details</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Company and contact details used for wholesale billing and invoices.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-[#F6821F]/10 text-[#F6821F] border border-[#F6821F]/20">
                Tier: {{ ucfirst($user->pricing_tier ?? 'Standard') }}
            </span>
        </div>

        <form action="{{ route('reseller.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Contact Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Company / Brand Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" placeholder="Your Hosting Brand"
                           class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" value="{{ $user->email }}" readonly
                           class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+880 1..."
                           class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="h-9 px-5 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg shadow-xs transition cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-xs">
        <div class="pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Change Account Password</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Keep your wholesale domain account protected with a strong password.</p>
        </div>

        <form action="{{ route('reseller.profile.password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Current Password</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                           class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                    @error('current_password')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">New Password</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                    @error('password')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="h-9 px-5 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-xs transition cursor-pointer">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Passkeys (WebAuthn) -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-xs" x-data="{
        registering: false,
        passkeyName: '',
        errorMessage: '',
        showPrompt: false,

        async registerPasskey() {
            if (!window.PublicKeyCredential) {
                this.errorMessage = 'Passkeys (WebAuthn) are not supported on this browser or platform.';
                return;
            }
            this.registering = true;
            this.errorMessage = '';

            try {
                let optRes = await fetch('{{ route('reseller.passkeys.register.options') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                let options = await optRes.json();

                options.challenge = Uint8Array.from(atob(options.challenge), c => c.charCodeAt(0));
                options.user.id = Uint8Array.from(atob(options.user.id), c => c.charCodeAt(0));

                let cred = await navigator.credentials.create({ publicKey: options });
                let rawId = btoa(String.fromCharCode(...new Uint8Array(cred.rawId)));
                let name = this.passkeyName.trim() || 'My Passkey Device';

                let saveRes = await fetch('{{ route('reseller.passkeys.store') }}', {
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
                    this.errorMessage = resData.message || 'Failed to register passkey.';
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
        <div class="flex items-start justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800 gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Passkeys & Biometric Authentication</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">WebAuthn / FIDO2</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Instant passwordless authentication via Touch ID, Face ID, or Windows Hello.</p>
            </div>
            <button type="button" @click="showPrompt = !showPrompt" class="h-8 px-3.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-[#F6821F] hover:text-[#F6821F] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Add Passkey</span>
            </button>
        </div>

        <div x-show="errorMessage" x-cloak class="mb-4 p-3 rounded-lg text-xs font-medium text-red-600 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800" x-text="errorMessage"></div>

        <!-- Add Passkey Input Dialog -->
        <div x-show="showPrompt" x-cloak class="mb-6 p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 space-y-3">
            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Register New Biometric Passkey Device</h4>
            <div class="flex flex-col sm:flex-row gap-2 max-w-md">
                <input type="text" x-model="passkeyName" placeholder="Device name (e.g. MacBook Touch ID)"
                       class="flex-1 px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                <button type="button" @click="registerPasskey()" :disabled="registering"
                        class="h-9 px-4 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg transition shrink-0 cursor-pointer disabled:opacity-50">
                    <span x-text="registering ? 'Scanning...' : 'Scan & Link'"></span>
                </button>
            </div>
            <p class="text-[11px] text-slate-500">Your phone or laptop will prompt for biometrics or security key.</p>
        </div>

        @if($passkeys->isEmpty())
            <div class="p-6 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-500">
                No passkeys linked yet. Click "Add Passkey" above to pair your device.
            </div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($passkeys as $pk)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-[#F6821F]/10 text-[#F6821F] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                                </svg>
                            </span>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $pk->name }}</p>
                                <p class="text-[11px] text-slate-500">
                                    Added {{ $pk->created_at->format('M d, Y') }} • Last used {{ $pk->last_used_at ? $pk->last_used_at->diffForHumans() : 'Never' }}
                                </p>
                            </div>
                        </div>
                        <form action="{{ route('reseller.passkeys.destroy', $pk->id) }}" method="POST" onsubmit="return confirm('Remove this passkey?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 text-xs text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded font-medium transition cursor-pointer">
                                Remove
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
