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

    
    public function applyPreset(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $preset = $request->input('preset');
        $domainName = strtolower($domain->domain_name);

        $presetsMap = [
            'google_workspace' => [
                ['type' => 'MX', 'host' => '@', 'value' => 'ASPMX.L.GOOGLE.COM.', 'priority' => 1, 'ttl' => 3600],
                ['type' => 'MX', 'host' => '@', 'value' => 'ALT1.ASPMX.L.GOOGLE.COM.', 'priority' => 5, 'ttl' => 3600],
                ['type' => 'MX', 'host' => '@', 'value' => 'ALT2.ASPMX.L.GOOGLE.COM.', 'priority' => 5, 'ttl' => 3600],
                ['type' => 'MX', 'host' => '@', 'value' => 'ALT3.ASPMX.L.GOOGLE.COM.', 'priority' => 10, 'ttl' => 3600],
                ['type' => 'MX', 'host' => '@', 'value' => 'ALT4.ASPMX.L.GOOGLE.COM.', 'priority' => 10, 'ttl' => 3600],
                ['type' => 'TXT', 'host' => '@', 'value' => 'v=spf1 include:_spf.google.com ~all', 'priority' => null, 'ttl' => 3600],
            ],
            'microsoft_365' => [
                ['type' => 'MX', 'host' => '@', 'value' => str_replace('.', '-', $domainName) . '.mail.protection.outlook.com.', 'priority' => 0, 'ttl' => 3600],
                ['type' => 'CNAME', 'host' => 'autodiscover', 'value' => 'autodiscover.outlook.com.', 'priority' => null, 'ttl' => 3600],
                ['type' => 'TXT', 'host' => '@', 'value' => 'v=spf1 include:spf.protection.outlook.com -all', 'priority' => null, 'ttl' => 3600],
            ],
            'cpanel_webmail' => [
                ['type' => 'CNAME', 'host' => 'mail', 'value' => $domainName . '.', 'priority' => null, 'ttl' => 14400],
                ['type' => 'MX', 'host' => '@', 'value' => 'mail.' . $domainName . '.', 'priority' => 0, 'ttl' => 14400],
                ['type' => 'TXT', 'host' => '@', 'value' => 'v=spf1 +a +mx ~all', 'priority' => null, 'ttl' => 14400],
            ],
        ];

        if (!isset($presetsMap[$preset])) {
            return back()->with('error', 'Unknown DNS preset selected.');
        }

        $recordsAdded = 0;
        foreach ($presetsMap[$preset] as $rec) {
            $exists = $domain->dnsRecords()
                ->where('type', $rec['type'])
                ->where('host', $rec['host'])
                ->where('value', $rec['value'])
                ->exists();

            if (!$exists) {
                $this->managementService->addDnsRecord($domain, $rec);
                $recordsAdded++;
            }
        }

        $presetLabel = match($preset) {
            'google_workspace' => 'Google Workspace (Gmail & SPF)',
            'microsoft_365' => 'Microsoft 365',
            'cpanel_webmail' => 'cPanel Webmail',
            default => 'Preset'
        };

        return back()->with('success', "Applied {$presetLabel} preset ({$recordsAdded} DNS records configured).");
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
