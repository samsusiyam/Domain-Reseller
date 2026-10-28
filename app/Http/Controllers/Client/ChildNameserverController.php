<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\ChildNameserver;
use App\Services\Domain\DomainManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChildNameserverController extends Controller
{
    public function __construct(
        protected DomainManagementService $managementService
    ) {}

    public function index(Domain $domain)
    {
        $this->authorizeDomain($domain);
        $childNameservers = $domain->childNameservers;

        return view('client.domains.child-nameservers', compact('domain', 'childNameservers'));
    }

    public function store(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $request->validate([
            'hostname' => ['required', 'string', 'max:150'],
            'ip_address' => ['required', 'ip'],
        ]);

        $fullHostname = str_ends_with($request->hostname, $domain->domain_name)
            ? $request->hostname
            : $request->hostname . '.' . $domain->domain_name;

        try {
            $this->managementService->createChildNameserver($domain, $fullHostname, $request->ip_address);
            return back()->with('success', "Child nameserver {$fullHostname} registered successfully.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Domain $domain, int $nsId)
    {
        $this->authorizeDomain($domain);
        $child = $domain->childNameservers()->findOrFail($nsId);

        try {
            $this->managementService->deleteChildNameserver($child);
            return back()->with('success', 'Child nameserver deleted successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
