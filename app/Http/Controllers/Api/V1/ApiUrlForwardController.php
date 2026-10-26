<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Domain;
use App\Models\UrlForward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiUrlForwardController extends BaseApiController
{
    /**
     * List URL redirect rules.
     */
    public function index(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $forwards = $domainModel->urlForwards()->get(['id', 'source_subdomain', 'destination_url', 'redirect_type', 'is_active']);

        return $this->sendResponse($forwards, 'URL redirect rules retrieved.');
    }

    /**
     * Add URL redirect rule.
     */
    public function store(Request $request, string $domain): JsonResponse
    {
        $request->validate([
            'source_subdomain' => ['nullable', 'string', 'max:100'],
            'destination_url'  => ['required', 'url', 'max:500'],
            'redirect_type'    => ['required', 'in:301,302,masked'],
        ]);

        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $forward = $domainModel->urlForwards()->create([
            'source_subdomain' => $request->input('source_subdomain') ?: '@',
            'destination_url'  => $request->input('destination_url'),
            'redirect_type'    => $request->input('redirect_type'),
            'is_active'        => true,
        ]);

        return $this->sendResponse($forward, 'URL redirect rule added successfully.', 201);
    }

    /**
     * Delete URL redirect rule.
     */
    public function destroy(Request $request, string $domain, string $forwardId): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $forward = UrlForward::where('domain_id', $domainModel->id)->findOrFail($forwardId);
        $forward->delete();

        return $this->sendResponse(null, 'URL redirect rule deleted.');
    }
}
