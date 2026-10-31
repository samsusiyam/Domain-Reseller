<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Domain;
use App\Models\EmailForward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiEmailForwardController extends BaseApiController
{
    /**
     * List email forwarders for domain.
     */
    public function index(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $forwards = $domainModel->emailForwards()->get(['id', 'source_prefix', 'destination_email', 'is_active']);

        return $this->sendResponse($forwards, 'Email forwarders retrieved.');
    }

    /**
     * Add email forwarder.
     */
    public function store(Request $request, string $domain): JsonResponse
    {
        $request->validate([
            'source_prefix'     => ['required', 'string', 'max:100'],
            'destination_email' => ['required', 'email', 'max:255'],
        ]);

        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $forward = $domainModel->emailForwards()->create([
            'source_prefix'     => strtolower(trim($request->input('source_prefix'))),
            'destination_email' => strtolower(trim($request->input('destination_email'))),
            'is_active'         => true,
        ]);

        try {
            Http::timeout(3)->get('http://127.0.0.1:9099/sync', ['domain' => $domainModel->domain_name]);
        } catch (\Throwable $e) {}

        return $this->sendResponse($forward, 'Email forwarder created successfully.', 201);
    }

    /**
     * Delete email forwarder.
     */
    public function destroy(Request $request, string $domain, string $forwardId): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $forward = EmailForward::where('domain_id', $domainModel->id)->findOrFail($forwardId);
        $forward->delete();

        try {
            Http::timeout(3)->get('http://127.0.0.1:9099/sync', ['domain' => $domainModel->domain_name]);
        } catch (\Throwable $e) {}

        return $this->sendResponse(null, 'Email forwarder removed.');
    }
}
