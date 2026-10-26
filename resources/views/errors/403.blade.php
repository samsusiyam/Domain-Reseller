@extends('layouts.guest')

@section('title', '403 Forbidden')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center">
        <div class="w-20 h-20 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-rose-600 font-black text-2xl shadow-sm">
            403
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Access Restricted</h1>
        <p class="text-sm text-slate-500 mt-2 mb-8 leading-relaxed">
            You do not have administrative or authorized permissions to view this resource. If you believe this is an error, please contact platform support.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                Return to Home
            </a>
            <a href="{{ route('client.tickets.create') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition text-sm">
                Contact Support
            </a>
        </div>
    </div>
</div>
@endsection
