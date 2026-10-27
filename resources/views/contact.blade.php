@extends('layouts.guest', ['title' => 'Contact Support & Sales'])

@section('content')
<div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">Contact support & sales</h1>
        <p class="text-sm mt-1" style="color: var(--text-muted);">
            Reach our wholesale registry engineering team for API integration, WHMCS configuration, or volume pricing.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-[6px] text-xs flex items-center gap-2.5 font-medium"
             style="background-color: var(--success-bg, #ECFDF5); border: 1px solid var(--success, #10B981); color: var(--success, #059669);">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-[6px] text-xs flex items-center gap-2.5 font-medium"
             style="background-color: #FEF2F2; border: 1px solid #EF4444; color: #DC2626;">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Sidebar Details Panel -->
        <div class="cf-panel p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-base font-semibold mb-2" style="color: var(--text);">Direct assistance</h2>
                <p class="text-xs leading-relaxed mb-6" style="color: var(--text-muted);">
                    Direct inquiries are answered during standard business hours. Critical domain registry provisioning alerts are monitored 24/7/365.
                </p>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="block text-[11px] font-medium uppercase tracking-wider mb-1" style="color: var(--text-muted);">Official Email</span>
                        <a href="mailto:support@hostnibo.com"
                           class="font-medium hover:underline flex items-center gap-2"
                           style="color: var(--text);">
                            <svg class="w-4 h-4 shrink-0 text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            <span>support@hostnibo.com</span>
                        </a>
                    </div>

                    <div>
                        <span class="block text-[11px] font-medium uppercase tracking-wider mb-1" style="color: var(--text-muted);">WhatsApp / Urgent</span>
                        <a href="https://wa.me/8801849693630" target="_blank" rel="noopener"
                           class="font-mono font-medium hover:underline flex items-center gap-2"
                           style="color: var(--text);">
                            <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                            <span>+880 1849-693630</span>
                        </a>
                    </div>

                    <div>
                        <span class="block text-[11px] font-medium uppercase tracking-wider mb-1" style="color: var(--text-muted);">Headquarters</span>
                        <div class="flex items-center gap-2" style="color: var(--text);">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>HostNibo Network, Bangladesh</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 mt-6 border-t" style="border-color: var(--border);">
                <div class="flex items-center gap-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Registry APIs 100% Operational</span>
                </div>
            </div>
        </div>

        <!-- Form Card Panel -->
        <div class="col-span-2 cf-panel p-6 sm:p-7">
            <form action="{{ route('contact.send') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Your name</label>
                        <input id="name"
                               type="text"
                               name="name"
                               value="{{ old('name', auth()->user()->name ?? '') }}"
                               required
                               placeholder="e.g. Siyam Ahmed"
                               class="cf-input h-10 w-full @error('name') border-[#DC2626] @enderror">
                        @error('name')
                            <p class="text-xs mt-1 font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Email address</label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email', auth()->user()->email ?? '') }}"
                               required
                               placeholder="name@hostnibo.com"
                               class="cf-input h-10 w-full @error('email') border-[#DC2626] @enderror">
                        @error('email')
                            <p class="text-xs mt-1 font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Phone / WhatsApp (Optional)</label>
                        <input id="phone"
                               type="text"
                               name="phone"
                               value="{{ old('phone') }}"
                               placeholder="+880 18..."
                               class="cf-input h-10 w-full @error('phone') border-[#DC2626] @enderror">
                        @error('phone')
                            <p class="text-xs mt-1 font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Subject</label>
                        <input id="subject"
                               type="text"
                               name="subject"
                               value="{{ old('subject') }}"
                               required
                               placeholder="WHMCS module setup or wholesale tier inquiry"
                               class="cf-input h-10 w-full @error('subject') border-[#DC2626] @enderror">
                        @error('subject')
                            <p class="text-xs mt-1 font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Message</label>
                    <textarea id="message"
                              name="message"
                              rows="5"
                              required
                              placeholder="Please describe your domain volume, registrar requirements, or issue details..."
                              class="cf-textarea w-full @error('message') border-[#DC2626] @enderror">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-xs mt-1 font-medium text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="cf-btn cf-btn-primary h-10 px-6 font-medium text-sm">
                        Send message
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
