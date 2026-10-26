@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">System Email Notification Templates</h1>
            <p class="text-xs text-slate-500 mt-1">Customize automated notification emails for domain registration, expiry reminders, invoices, and password resets.</p>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Template Identifier</th>
                        <th class="px-6 py-3.5">Default Subject Line</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($templates as $tmpl)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 block">{{ $tmpl->name }}</span>
                                <span class="font-mono text-xs text-slate-400">{{ $tmpl->type ?? 'general' }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 font-medium">
                                {{ $tmpl->subject }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $tmpl->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $tmpl->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.email-templates.edit', $tmpl->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">
                                    Edit Template &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
