@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
            Reach Our Technical Team
        </span>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mt-4">Get in Touch</h1>
        <p class="mt-3 text-sm text-slate-400">
            Have questions about wholesale pricing tiers, API provisioning, or high-volume registrar accounts? We're here to help.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        <!-- Contact Info Sidebar -->
        <div class="glass-card p-8 rounded-3xl border border-white/10 flex flex-col justify-between">
            <div>
                <h2 class="text-xl font-bold text-white mb-4">Direct Support</h2>
                <p class="text-xs sm:text-sm text-slate-300 mb-8 leading-relaxed">
                    We respond to wholesale partnership requests within 2-4 hours. Active client ticket support is available 24/7/365 in the portal.
                </p>

                <div class="space-y-4 text-xs font-mono">
                    <div class="flex items-center gap-3 text-slate-300">
                        <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ config('mail.from.address', 'support@domainreseller.local') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-300">
                        <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Dhaka, Bangladesh / Cloud Infrastructure</span>
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t border-white/10 text-[11px] text-slate-400 font-mono">
                Active Client Portal: Online
            </div>
        </div>

        <!-- Form Card -->
        <div class="col-span-2 glass-card p-8 rounded-3xl border border-white/10">
            <form action="{{ route('contact.send') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Your Name</label>
                        <input type="text" name="name" required
                            class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500" placeholder="Rakib Hasan">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500" placeholder="rakib@mycompany.com">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Subject</label>
                    <input type="text" name="subject" required
                        class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500" placeholder="Inquiry about WHMCS module & wholesale rates">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Message</label>
                    <textarea name="message" rows="5" required
                        class="w-full p-4 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500" placeholder="Tell us about your estimated monthly domain volume and billing platform..."></textarea>
                </div>

                <button type="submit"
                    class="px-8 py-3.5 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold rounded-xl transition shadow-lg shadow-indigo-600/30 text-sm">
                    Send Message →
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
