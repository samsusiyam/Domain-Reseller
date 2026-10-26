<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Domain;
use Illuminate\Http\Request;

class SubClientController extends Controller
{
    /**
     * Lists end-clients / WHOIS contacts registered under reseller orders.
     */
    public function index(Request $request)
    {
        $reseller = $request->user();

        $contacts = Contact::where('user_id', $reseller->id)
            ->latest()
            ->paginate(15);

        return view('reseller.sub-clients.index', compact('contacts'));
    }

    /**
     * Show sub-client profile and associated domains.
     */
    public function show(Request $request, $id)
    {
        $contact = Contact::where('user_id', $request->user()->id)->findOrFail($id);

        $domains = Domain::where('user_id', $request->user()->id)
            ->where(function ($q) use ($contact) {
                $q->where('registrant_contact_id', $contact->id)
                  ->orWhere('admin_contact_id', $contact->id)
                  ->orWhere('tech_contact_id', $contact->id)
                  ->orWhere('billing_contact_id', $contact->id);
            })
            ->get();

        return view('reseller.sub-clients.show', compact('contact', 'domains'));
    }

    /**
     * Toggle status (or delete) sub-client contact.
     */
    public function toggleStatus(Request $request, $id)
    {
        $contact = Contact::where('user_id', $request->user()->id)->findOrFail($id);
        $contact->update(['is_default' => !$contact->is_default]);

        return back()->with('success', 'Sub-client contact updated.');
    }
}
