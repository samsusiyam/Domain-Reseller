<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DnsRecord;
use App\Models\Domain;
use App\Services\Domain\DomainManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDnsController extends BaseApiController
{
    public function __construct(
        protected DomainManagementService $managementService
    ) {}

    /**
     * Get all DNS zone records.
     */
    public function index(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $records = $domainModel->dnsRecords()->get(['id', 'type', 'host', 'value', 'priority', 'ttl']);

        return $this->sendResponse($records, 'DNS zone records retrieved.');
    }

    /**
     * Add new DNS record.
     */
    public function store(Request $request, string $domain): JsonResponse
    {
        $request->validate([
            'type'     => ['required', 'string', 'in:A,AAAA,CNAME,MX,TXT,SRV,NS,CAA'],
            'host'     => ['required', 'string', 'max:255'],
            'value'    => ['required', 'string', 'max:500'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'ttl'      => ['nullable', 'integer', 'min:60'],
        ]);

        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $record = $this->managementService->addDnsRecord($domainModel, [
            'type'     => strtoupper($request->input('type')),
            'host'     => trim($request->input('host')),
            'value'    => trim($request->input('value')),
            'priority' => $request->input('priority'),
            'ttl'      => $request->input('ttl', 3600),
        ]);

        return $this->sendResponse($record, 'DNS record added successfully.', 201);
    }

    /**
     * Update existing DNS record.
     */
    public function update(Request $request, string $domain, string $recordId): JsonResponse
    {
        $request->validate([
            'type'     => ['required', 'string', 'in:A,AAAA,CNAME,MX,TXT,SRV,NS,CAA'],
            'host'     => ['required', 'string', 'max:255'],
            'value'    => ['required', 'string', 'max:500'],
            'priority' => ['nullable', 'integer'],
            'ttl'      => ['nullable', 'integer', 'min:60'],
        ]);

        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $record = DnsRecord::where('domain_id', $domainModel->id)->findOrFail($recordId);

        $this->managementService->updateDnsRecord($record, [
            'type'     => strtoupper($request->input('type')),
            'host'     => trim($request->input('host')),
            'value'    => trim($request->input('value')),
            'priority' => $request->input('priority'),
            'ttl'      => $request->input('ttl', 3600),
        ]);

        return $this->sendResponse($record->fresh(), 'DNS record updated successfully.');
    }

    /**
     * Delete DNS record.
     */
    public function destroy(Request $request, string $domain, string $recordId): JsonResponse
    {
        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $record = DnsRecord::where('domain_id', $domainModel->id)->findOrFail($recordId);
        $this->managementService->deleteDnsRecord($record);

        return $this->sendResponse(null, 'DNS record deleted successfully.');
    }
}
