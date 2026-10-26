@extends('layouts.guest')

@section('content')
<div class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('knowledgebase.index') }}" class="text-xs font-bold text-indigo-600 hover:underline mb-6 inline-flex items-center gap-1">
            &larr; Back to Knowledgebase
        </a>

        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight capitalize mb-6">
            {{ str_replace('-', ' ', $slug) }}
        </h1>

        <div class="prose prose-slate max-w-none text-base text-slate-700 leading-relaxed border-t border-slate-100 pt-6 space-y-6">
            <p>
                Welcome to our guide for <strong>{{ ucwords(str_replace('-', ' ', $slug)) }}</strong>. Below are the key steps and technical references for configuring your domains and accounts.
            </p>

            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200">
                <h3 class="text-base font-bold text-slate-900 mb-2">WHMCS Integration Overview</h3>
                <p class="text-sm text-slate-600">
                    If you are setting up our official registrar module in your WHMCS installation:
                </p>
                <ol class="list-decimal pl-5 mt-2 space-y-1.5 text-sm text-slate-700">
                    <li>Download the official module zip package from your reseller dashboard.</li>
                    <li>Extract to <code>modules/registrars/domainreseller/</code> inside your WHMCS root.</li>
                    <li>Navigate to <em>Setup &gt; Products/Services &gt; Domain Registrars</em> and activate <strong>DomainReseller</strong>.</li>
                    <li>Paste your <code>API Key</code> and <code>API Secret</code> generated from the reseller portal.</li>
                </ol>
            </div>

            <p class="text-sm text-slate-600">
                Need more help? Our 24/7 technical team is ready to assist via support tickets in your client dashboard.
            </p>
        </div>
    </div>
</div>
@endsection
