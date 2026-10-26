<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Domain;
use App\Services\Domain\DomainManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiNameserverController extends BaseApiController
{
    public function __construct(
        protected DomainManagementService $managementService
    ) {}

    /**
     * Get authoritative nameservers for domain.
     */
    public function index(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found in your account.", [], 404);
        }

        return $this->sendResponse([
            'domain'      => $domainModel->domain_name,
            'nameservers' => $domainModel->nameservers ?? [],
        ], 'Nameservers retrieved.');
    }

    /**
     * Update authoritative nameservers for domain.
     */
    public function update(Request $request, string $domain): JsonResponse
    {
        $request->validate([
            'nameservers'   => ['required', 'array', 'min:2', 'max:5'],
            'nameservers.*' => ['required', 'string', 'max:255'],
        ]);

        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $nameservers = array_values(array_filter($request->input('nameservers')));

        try {
            $success = $this->managementService->updateNameservers($domainModel, $nameservers);

            if ($success) {
                return $this->sendResponse([
                    'domain'      => $domainModel->domain_name,
                    'nameservers' => $nameservers,
                ], 'Authoritative nameservers updated successfully.');
            }

            return $this->sendError('Failed to update nameservers at registrar module.', [], 500);
        } catch (\Throwable $e) {
            return $this->sendError('Nameserver update failed: ' . $e->getMessage(), [], 500);
        }
    }
}
