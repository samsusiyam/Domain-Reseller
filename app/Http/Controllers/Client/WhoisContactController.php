<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Contact;
use App\Services\Domain\DomainManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhoisContactController extends Controller
{
    public function __construct(
        protected DomainManagementService $managementService
    ) {}

    public function edit(Domain $domain)
    {
        $this->authorizeDomain($domain);
        $userContacts = Auth::user()->contacts;

        $liveWhois = null;
        try {
            $liveWhois = $this->managementService->getWhois($domain);
        } catch (\Throwable $e) {
        }

        return view('client.domains.whois', compact('domain', 'userContacts', 'liveWhois'));
    }

    public function update(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $validated = $request->validate([
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:255'],
            'phone_number'   => ['required', 'string', 'max:50'],
            'company_name'   => ['nullable', 'string', 'max:150'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:100'],
            'state'          => ['required', 'string', 'max:100'],
            'postal_code'    => ['required', 'string', 'max:20'],
            'country'        => ['required', 'string', 'size:2'],
        ]);

        $validated['country'] = strtoupper($validated['country']);

        // Update or create local contact record
        $contact = Contact::updateOrCreate(
            ['id' => $domain->registrant_contact_id],
            array_merge($validated, [
                'user_id'      => Auth::id(),
                'contact_type' => 'registrant',
            ])
        );

        $domain->update([
            'registrant_contact_id' => $contact->id,
            'admin_contact_id'      => $contact->id,
            'tech_contact_id'       => $contact->id,
            'billing_contact_id'    => $contact->id,
        ]);

        // Push update to live registry via registrar driver
        $pushed = $this->managementService->updateWhois($domain, $validated);

        if ($pushed) {
            return back()->with('success', 'WHOIS contact information updated successfully at registry.');
        }

        return back()->with('success', 'WHOIS contact information saved locally.');
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
