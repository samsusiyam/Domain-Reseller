@extends('layouts.app', ['title' => 'Contact Profiles', 'header' => 'Contact Profiles'])

@section('content')
<div class="space-y-6">
    <!-- Header with Add Contact Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">ICANN Registrant & Contact Profiles</h1>
            <p class="text-xs text-slate-500 mt-1">Manage verified identity profiles used for domain WHOIS Registrant, Admin, Tech, and Billing records.</p>
        </div>
        <a href="{{ route('client.contacts.create') }}" class="h-9 px-4 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg flex items-center gap-1.5 transition shadow-sm self-start sm:self-auto cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Add contact profile</span>
        </a>
    </div>

    <!-- ICANN Requirement Information Callout -->
    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-600 dark:text-slate-400 space-y-1">
        <div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
            <svg class="w-4 h-4 text-[#F6821F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
            <span>ICANN Accuracy Specification</span>
        </div>
        <p class="text-[11px] leading-relaxed">
            ICANN requires valid name, email, physical address, and phone number on all domain registrations. You can create multiple contact profiles to reuse during domain checkout or assign across your portfolio.
        </p>
    </div>

    @if($contacts->isEmpty())
        <!-- Empty State -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto mb-3 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">No contact profiles created yet</h3>
            <p class="text-xs text-slate-500 mt-1 mb-6 max-w-sm mx-auto">Create your first contact profile to pre-fill WHOIS registrant data automatically when registering or transferring domains.</p>
            <a href="{{ route('client.contacts.create') }}" class="h-9 px-4 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg inline-flex items-center gap-1.5 transition">
                Create first profile
            </a>
        </div>
    @else
        <!-- Contacts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($contacts as $contact)
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between relative">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $contact->first_name }} {{ $contact->last_name }}</h4>
                                    @if($contact->is_default)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#F6821F]/10 text-[#F6821F] border border-[#F6821F]/20 uppercase">Default</span>
                                    @endif
                                </div>
                                @if($contact->company_name)
                                    <p class="text-xs font-medium text-slate-600 dark:text-slate-400 mt-0.5">{{ $contact->company_name }}</p>
                                @endif
                            </div>
                            <span class="text-[11px] font-mono uppercase px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                                {{ $contact->contact_type }}
                            </span>
                        </div>

                        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-3">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400 w-16">Email:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $contact->email }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400 w-16">Phone:</span>
                                <span class="font-mono text-slate-800 dark:text-slate-200">+{{ $contact->phone_country_code }} {{ $contact->phone_number }}</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-slate-400 w-16 shrink-0">Address:</span>
                                <span class="text-slate-800 dark:text-slate-200 leading-relaxed">
                                    {{ $contact->address_line_1 }}{{ $contact->address_line_2 ? ', ' . $contact->address_line_2 : '' }},
                                    {{ $contact->city }}, {{ $contact->state }} {{ $contact->postal_code }}, {{ $contact->country }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            @if(!$contact->is_default)
                                <form action="{{ route('client.contacts.set-default', $contact->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-slate-500 hover:text-[#F6821F] font-medium transition cursor-pointer">
                                        Set as default
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('client.contacts.edit', $contact->id) }}" class="text-slate-600 dark:text-slate-400 hover:text-[#F6821F] font-medium transition">
                                Edit
                            </a>
                            <form action="{{ route('client.contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Remove this contact profile?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium transition cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
