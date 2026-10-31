<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ChildNameserver;
use App\Models\Domain;
use App\Services\Domain\DomainManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiChildNameserverController extends BaseApiController
{
    public function __construct(
        protected DomainManagementService $managementService
    ) {}

    /**
     * List registered child host glue records.
     */
    public function index(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $records = $domainModel->childNameservers()->get(['id', 'hostname', 'ip_address']);

        return $this->sendResponse($records, 'Child nameservers retrieved.');
    }

    /**
     * Register new child nameserver.
     */
    public function store(Request $request, string $domain): JsonResponse
    {
        $request->validate([
            'hostname'   => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'ip'],
        ]);

        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $hostname = strtolower(trim($request->input('hostname')));
        $ip = trim($request->input('ip_address'));

        try {
            $child = $this->managementService->createChildNameserver($domainModel, $hostname, $ip);

            return $this->sendResponse([
                'id'         => $child->id,
                'hostname'   => $child->hostname,
                'ip_address' => $child->ip_address,
            ], 'Child nameserver registered successfully.', 201);
        } catch (\Throwable $e) {
            return $this->sendError('Failed to register child nameserver: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update existing child nameserver IP address.
     */
    public function update(Request $request, string $domain, string $hostname): JsonResponse
    {
        $request->validate(['ip_address' => ['required', 'ip']]);

        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $child = ChildNameserver::where('domain_id', $domainModel->id)
            ->where('hostname', strtolower(trim($hostname)))
            ->first();

        if (!$child) {
            return $this->sendError("Child nameserver '{$hostname}' not found.", [], 404);
        }

        $this->managementService->updateChildNameserver($child, trim($request->input('ip_address')));

        return $this->sendResponse([
            'id'         => $child->id,
            'hostname'   => $child->hostname,
            'ip_address' => $child->ip_address,
        ], 'Child nameserver IP updated.');
    }

    /**
     * Delete child nameserver.
     */
    public function destroy(Request $request, string $domain, string $hostname): JsonResponse
    {
        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $child = ChildNameserver::where('domain_id', $domainModel->id)
            ->where('hostname', strtolower(trim($hostname)))
            ->first();

        if (!$child) {
            return $this->sendError("Child nameserver '{$hostname}' not found.", [], 404);
        }

        $this->managementService->deleteChildNameserver($child);

        return $this->sendResponse(null, 'Child nameserver removed successfully.');
    }
}
