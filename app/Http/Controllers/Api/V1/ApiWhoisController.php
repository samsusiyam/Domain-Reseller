<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Contact;
use App\Models\Domain;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiWhoisController extends BaseApiController
{
    public function __construct(
        protected RegistrarManager $registrarManager
    ) {}

    /**
     * Get WHOIS contact details for domain.
     */
    public function show(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::with(['registrantContact', 'adminContact', 'techContact', 'billingContact'])
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found in your account.", [], 404);
        }

        return $this->sendResponse([
            'domain'     => $domainModel->domain_name,
            'is_private' => $domainModel->is_private,
            'registrant' => $domainModel->registrantContact,
            'admin'      => $domainModel->adminContact ?? $domainModel->registrantContact,
            'tech'       => $domainModel->techContact ?? $domainModel->registrantContact,
            'billing'    => $domainModel->billingContact ?? $domainModel->registrantContact,
        ], 'WHOIS contacts retrieved.');
    }

    /**
     * Update WHOIS contact details at registry.
     */
    public function update(Request $request, string $domain): JsonResponse
    {
        $request->validate([
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:50'],
            'address_line_1'=> ['required', 'string', 'max:255'],
            'city'         => ['required', 'string', 'max:100'],
            'state'        => ['required', 'string', 'max:100'],
            'country'      => ['required', 'string', 'size:2'],
            'postal_code'  => ['required', 'string', 'max:20'],
        ]);

        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $contact = Contact::create([
            'user_id'            => $request->user()->id,
            'contact_type'       => 'registrant',
            'first_name'         => $request->input('first_name'),
            'last_name'          => $request->input('last_name'),
            'company_name'       => $request->input('company_name'),
            'email'              => $request->input('email'),
            'phone_country_code' => $request->input('phone_country_code', '+1'),
            'phone_number'       => $request->input('phone_number'),
            'address_line_1'     => $request->input('address_line_1'),
            'city'               => $request->input('city'),
            'state'              => $request->input('state'),
            'country'            => strtoupper($request->input('country')),
            'postal_code'        => $request->input('postal_code'),
        ]);

        $domainModel->update(['registrant_contact_id' => $contact->id]);

        try {
            $dto = new \App\DTOs\WhoisDataDTO(
                registrant: $contact->toArray(),
                admin:      $contact->toArray(),
                tech:       $contact->toArray(),
                billing:    $contact->toArray()
            );
            $this->registrarManager->driver($domainModel->registrar->slug)->updateWhois($domainModel->domain_name, $dto);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("ApiWhoisController updateWhois error: " . $e->getMessage());
        }

        return $this->sendResponse($contact, 'WHOIS contact details updated successfully.');
    }

    /**
     * Public / Reseller WHOIS lookup.
     */
    public function lookup(Request $request, string $domain): JsonResponse
    {
        $domain = strtolower(trim($domain));
        $domainModel = Domain::where('domain_name', $domain)->first();

        if ($domainModel) {
            return $this->sendResponse([
                'domain_name'  => $domainModel->domain_name,
                'status'       => $domainModel->status,
                'created_date' => $domainModel->registration_date?->toIso8601String(),
                'expires_date' => $domainModel->expires_at?->toIso8601String(),
                'nameservers'  => $domainModel->nameservers,
                'is_locked'    => $domainModel->is_locked,
                'is_private'   => $domainModel->is_private,
            ], 'WHOIS record found in registry.');
        }

        return $this->sendResponse([
            'domain_name' => $domain,
            'status'      => 'unregistered_or_external',
        ], 'Domain WHOIS queried.');
    }
}
