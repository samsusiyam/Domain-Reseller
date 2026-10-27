<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientContactController extends Controller
{
    /**
     * Display a listing of client saved contact profiles.
     */
    public function index()
    {
        $contacts = Auth::user()->contacts()->latest()->get();
        return view('client.contacts.index', compact('contacts'));
    }

    /**
     * Show form to create a new contact profile.
     */
    public function create()
    {
        return view('client.contacts.create');
    }

    /**
     * Store a newly created contact profile.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_type'       => ['required', 'in:individual,company'],
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'company_name'       => ['nullable', 'string', 'max:150'],
            'email'              => ['required', 'email', 'max:191'],
            'phone_country_code' => ['required', 'string', 'max:5'],
            'phone_number'       => ['required', 'string', 'max:25'],
            'address_line_1'     => ['required', 'string', 'max:255'],
            'address_line_2'     => ['nullable', 'string', 'max:255'],
            'city'               => ['required', 'string', 'max:100'],
            'state'              => ['required', 'string', 'max:100'],
            'postal_code'        => ['required', 'string', 'max:20'],
            'country'            => ['required', 'string', 'size:2'],
            'is_default'         => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();

        // If this contact is marked default, or if it is user's first contact, mark as default
        $isFirst = $user->contacts()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        if ($isDefault) {
            $user->contacts()->update(['is_default' => false]);
        }

        $validated['user_id'] = $user->id;
        $validated['is_default'] = $isDefault;

        Contact::create($validated);

        return redirect()->route('client.contacts.index')->with('success', 'Contact profile created successfully.');
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact)
    {
        $this->authorizeContact($contact);
        return view('client.contacts.edit', compact('contact'));
    }

    /**
     * Update the specified contact profile.
     */
    public function update(Request $request, Contact $contact)
    {
        $this->authorizeContact($contact);

        $validated = $request->validate([
            'contact_type'       => ['required', 'in:individual,company'],
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'company_name'       => ['nullable', 'string', 'max:150'],
            'email'              => ['required', 'email', 'max:191'],
            'phone_country_code' => ['required', 'string', 'max:5'],
            'phone_number'       => ['required', 'string', 'max:25'],
            'address_line_1'     => ['required', 'string', 'max:255'],
            'address_line_2'     => ['nullable', 'string', 'max:255'],
            'city'               => ['required', 'string', 'max:100'],
            'state'              => ['required', 'string', 'max:100'],
            'postal_code'        => ['required', 'string', 'max:20'],
            'country'            => ['required', 'string', 'size:2'],
            'is_default'         => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            Auth::user()->contacts()->where('id', '!=', $contact->id)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $contact->update($validated);

        return redirect()->route('client.contacts.index')->with('success', 'Contact profile updated successfully.');
    }

    /**
     * Set contact as default.
     */
    public function setDefault(Contact $contact)
    {
        $this->authorizeContact($contact);

        Auth::user()->contacts()->update(['is_default' => false]);
        $contact->update(['is_default' => true]);

        return redirect()->route('client.contacts.index')->with('success', 'Default contact profile updated.');
    }

    /**
     * Remove the specified contact profile.
     */
    public function destroy(Contact $contact)
    {
        $this->authorizeContact($contact);
        $contact->delete();

        return redirect()->route('client.contacts.index')->with('success', 'Contact profile removed.');
    }

    protected function authorizeContact(Contact $contact): void
    {
        if ($contact->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
