@extends('layouts.guest')

@section('title', '500 Server Error')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center">
        <div class="w-20 h-20 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-amber-600 font-black text-2xl shadow-sm">
            500
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">System Encountered An Issue</h1>
        <p class="text-sm text-slate-500 mt-2 mb-8 leading-relaxed">
            Our engineering team has been notified. This is typically a temporary timeout with an upstream registrar or payment gateway. Please try again shortly.
        </p>
        <div class="flex items-center justify-center gap-3">
            <button onclick="window.location.reload()" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                Reload Page
            </button>
            <a href="{{ url('/') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition text-sm">
                Return to Home
            </a>
        </div>
    </div>
</div>
@endsection
