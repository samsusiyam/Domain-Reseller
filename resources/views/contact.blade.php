@extends('layouts.guest')

@section('content')
<div class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">Get in Touch</h1>
            <p class="mt-2 text-sm text-slate-600">
                Have questions about our wholesale tiers or custom API integrations? Our technical team is here to assist.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Contact Info Sidebar -->
            <div class="bg-indigo-900 text-white p-8 rounded-2xl shadow-xl flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold mb-4">Direct Support</h3>
                    <p class="text-sm text-indigo-200 mb-8 leading-relaxed">
                        We respond to inquiries within 2-4 hours during business days. For urgent billing issues, ticket support is available 24/7 in the client dashboard.
                    </p>

                    <div class="space-y-4 text-sm">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{{ config('mail.from.address', 'support@domainreseller.com') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Dhaka, Bangladesh / Global CDN</span>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-indigo-800 text-xs text-indigo-300">
                    Client Portal: Open 24/7/365
                </div>
            </div>

            <!-- Form -->
            <div class="col-span-2 bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                <form action="{{ route('contact.send') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Your Name</label>
                            <input type="text" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="John Doe">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                            <input type="email" name="email" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="john@example.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Subject</label>
                        <input type="text" name="subject" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Wholesale Inquiry / API Access">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Message</label>
                        <textarea name="message" rows="5" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Tell us about your expected monthly volume or question..."></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
