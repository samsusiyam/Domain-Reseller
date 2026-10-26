<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhoisContactController extends Controller
{
    public function edit(Domain $domain)
    {
        $this->authorizeDomain($domain);
        $userContacts = Auth::user()->contacts;

        return view('client.domains.whois', compact('domain', 'userContacts'));
    }

    public function update(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $request->validate([
            'registrant_contact_id' => 'required|exists:contacts,id',
            'admin_contact_id' => 'nullable|exists:contacts,id',
            'tech_contact_id' => 'nullable|exists:contacts,id',
            'billing_contact_id' => 'nullable|exists:contacts,id',
        ]);

        $domain->update([
            'registrant_contact_id' => $request->registrant_contact_id,
            'admin_contact_id' => $request->admin_contact_id ?? $request->registrant_contact_id,
            'tech_contact_id' => $request->tech_contact_id ?? $request->registrant_contact_id,
            'billing_contact_id' => $request->billing_contact_id ?? $request->registrant_contact_id,
        ]);

        return back()->with('success', 'WHOIS contact information updated.');
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
