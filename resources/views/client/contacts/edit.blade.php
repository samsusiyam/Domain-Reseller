@extends('layouts.app', ['title' => 'Edit Contact Profile', 'header' => 'Edit Contact Profile'])

@section('content')
<div class="max-w-3xl bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
    <div class="mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Edit contact profile</h2>
        <p class="text-xs text-slate-500 mt-1">Update contact details for WHOIS registry records.</p>
    </div>

    <form action="{{ route('client.contacts.update', $contact->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Contact Type -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Profile type</label>
            <div class="flex items-center gap-4 text-xs">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="contact_type" value="individual" {{ old('contact_type', $contact->contact_type) === 'individual' ? 'checked' : '' }} class="accent-[#F6821F]">
                    <span class="text-slate-800 dark:text-slate-200 font-medium">Individual / Personal</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="contact_type" value="company" {{ old('contact_type', $contact->contact_type) === 'company' ? 'checked' : '' }} class="accent-[#F6821F]">
                    <span class="text-slate-800 dark:text-slate-200 font-medium">Company / Organization</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">First name</label>
                <input type="text" name="first_name" value="{{ old('first_name', $contact->first_name) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Last name</label>
                <input type="text" name="last_name" value="{{ old('last_name', $contact->last_name) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                @error('last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Organization / Company name (optional)</label>
            <input type="text" name="company_name" value="{{ old('company_name', $contact->company_name) }}" placeholder="e.g. Acme Corporation" class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email address</label>
                <input type="email" name="email" value="{{ old('email', $contact->email) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Country code</label>
                <input type="text" name="phone_country_code" value="{{ old('phone_country_code', $contact->phone_country_code) }}" placeholder="880" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone number</label>
            <input type="text" name="phone_number" value="{{ old('phone_number', $contact->phone_number) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            @error('phone_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Address line 1</label>
            <input type="text" name="address_line_1" value="{{ old('address_line_1', $contact->address_line_1) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Address line 2 (optional)</label>
            <input type="text" name="address_line_2" value="{{ old('address_line_2', $contact->address_line_2) }}" class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">City</label>
                <input type="text" name="city" value="{{ old('city', $contact->city) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">State / Province</label>
                <input type="text" name="state" value="{{ old('state', $contact->state) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Postcode / ZIP</label>
                <input type="text" name="postal_code" value="{{ old('postal_code', $contact->postal_code) }}" required class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Country (ISO 2)</label>
                <input type="text" name="country" value="{{ old('country', $contact->country) }}" maxlength="2" required class="w-full px-3.5 py-2 text-xs font-mono uppercase border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            </div>
        </div>

        <div class="pt-2">
            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="is_default" value="1" {{ old('is_default', $contact->is_default) ? 'checked' : '' }} class="accent-[#F6821F] rounded">
                <span>Set this profile as default for all new domain orders</span>
            </label>
        </div>

        <div class="pt-4 flex items-center gap-3">
            <button type="submit" class="h-9 px-5 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg shadow-sm transition">
                Update contact profile
            </button>
            <a href="{{ route('client.contacts.index') }}" class="h-9 px-4 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium rounded-lg flex items-center hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
