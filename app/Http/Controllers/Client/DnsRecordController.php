<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\DnsRecord;
use App\Services\Domain\DomainManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DnsRecordController extends Controller
{
    public function __construct(
        protected DomainManagementService $managementService
    ) {}

    public function index(Domain $domain)
    {
        $this->authorizeDomain($domain);
        $records = $domain->dnsRecords()->orderBy('type')->get();

        return view('client.domains.dns', compact('domain', 'records'));
    }

    public function store(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $validated = $request->validate([
            'type' => ['required', 'in:A,AAAA,CNAME,MX,TXT,NS,SRV,CAA'],
            'host' => ['required', 'string', 'max:150'],
            'value' => ['required', 'string'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'ttl' => ['required', 'integer', 'min:60', 'max:86400'],
        ]);

        $this->managementService->addDnsRecord($domain, $validated);

        return back()->with('success', 'DNS record added successfully.');
    }

    public function update(Request $request, Domain $domain, int $recordId)
    {
        $this->authorizeDomain($domain);
        $record = $domain->dnsRecords()->findOrFail($recordId);

        $validated = $request->validate([
            'host' => ['required', 'string', 'max:150'],
            'value' => ['required', 'string'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'ttl' => ['required', 'integer', 'min:60', 'max:86400'],
        ]);

        $this->managementService->updateDnsRecord($record, $validated);

        return back()->with('success', 'DNS record updated successfully.');
    }

    public function destroy(Domain $domain, int $recordId)
    {
        $this->authorizeDomain($domain);
        $record = $domain->dnsRecords()->findOrFail($recordId);

        $this->managementService->deleteDnsRecord($record);

        return back()->with('success', 'DNS record deleted.');
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
