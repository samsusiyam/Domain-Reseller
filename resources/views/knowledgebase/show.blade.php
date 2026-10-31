@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('knowledgebase.index') }}" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1.5 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to All Knowledgebase Categories</span>
        </a>
    </div>

    <article class="glass-card p-8 sm:p-12 rounded-3xl border border-white/10 shadow-2xl">
        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
            Guide
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight capitalize mt-4 mb-8 leading-tight">
            {{ str_replace('-', ' ', $slug) }}
        </h1>

        <div class="prose prose-invert prose-indigo max-w-none text-sm sm:text-base text-slate-300 leading-relaxed border-t border-white/10 pt-6 space-y-6">
            <p>
                Welcome to our technical documentation for <strong>{{ ucwords(str_replace('-', ' ', $slug)) }}</strong>. Below are the key steps, architectural requirements, and configuration instructions.
            </p>

            <div class="p-6 rounded-2xl bg-obsidian-900 border border-white/10 space-y-3">
                <h3 class="text-base font-bold text-white mb-2">Step-by-Step Implementation</h3>
                <ol class="list-decimal pl-5 space-y-2 text-sm text-slate-300">
                    <li>Download the official WHMCS registrar module archive from your Reseller Portal.</li>
                    <li>Extract and upload to <code class="text-indigo-300 bg-white/5 px-2 py-0.5 rounded font-mono text-xs">modules/registrars/domainreseller/</code> inside your WHMCS root.</li>
                    <li>Navigate to <em>WHMCS Admin ➔ System Settings ➔ Domain Registrars</em> and click <strong>Activate</strong> next to DomainReseller.</li>
                    <li>Enter your <strong>API Key</strong> and <strong>API Secret</strong>, save changes, and link your desired TLDs.</li>
                </ol>
            </div>

            <p class="text-xs text-slate-400">
                Need extra assistance? You can open a ticket with our support engineers from your Client Dashboard.
            </p>
        </div>
    </article>
</div>
@endsection
