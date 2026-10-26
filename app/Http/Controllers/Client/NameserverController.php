<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\Domain\DomainManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NameserverController extends Controller
{
    public function __construct(
        protected DomainManagementService $managementService
    ) {}

    public function edit(Domain $domain)
    {
        $this->authorizeDomain($domain);
        return view('client.domains.nameservers', compact('domain'));
    }

    public function update(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $request->validate([
            'nameservers' => ['required', 'array', 'min:2', 'max:5'],
            'nameservers.*' => ['required', 'string', 'regex:/^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/'],
        ]);

        $this->managementService->updateNameservers($domain, $request->nameservers);

        return back()->with('success', 'Authoritative nameservers updated successfully. DNS changes may take up to 24-48 hours to propagate.');
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
