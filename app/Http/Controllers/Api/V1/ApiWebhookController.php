<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Webhook;
use App\Jobs\DispatchWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiWebhookController extends BaseApiController
{
    /**
     * List registered webhooks.
     */
    public function index(Request $request): JsonResponse
    {
        $webhooks = Webhook::where('user_id', $request->user()->id)->get();
        return $this->sendResponse($webhooks, 'Webhooks retrieved.');
    }

    /**
     * Register new webhook.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'target_url'        => ['required', 'url', 'max:255'],
            'subscribed_events' => ['required', 'array', 'min:1'],
        ]);

        $webhook = Webhook::create([
            'user_id'           => $request->user()->id,
            'target_url'        => $request->input('target_url'),
            'secret_key'        => 'whsec_' . Str::random(32),
            'subscribed_events' => $request->input('subscribed_events'),
            'is_active'         => true,
            'failure_count'     => 0,
        ]);

        return $this->sendResponse($webhook, 'Webhook created successfully.', 201);
    }

    /**
     * Delete registered webhook.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $webhook = Webhook::where('user_id', $request->user()->id)->findOrFail($id);
        $webhook->delete();

        return $this->sendResponse(null, 'Webhook deleted.');
    }

    /**
     * Dispatch mock event to webhook.
     */
    public function test(Request $request, $id): JsonResponse
    {
        $webhook = Webhook::where('user_id', $request->user()->id)->findOrFail($id);

        $testPayload = [
            'event'     => 'domain.test_ping',
            'timestamp' => now()->toIso8601String(),
            'domain'    => 'test-example.com',
            'message'   => 'Automated test ping from DomainReseller API.',
        ];

        DispatchWebhookJob::dispatch($webhook->id, 'domain.test_ping', $testPayload);

        return $this->sendResponse(['queued' => true], 'Test ping dispatched.');
    }
}
