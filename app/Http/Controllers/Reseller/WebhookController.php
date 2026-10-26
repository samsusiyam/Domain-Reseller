<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Jobs\DispatchWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    /**
     * List all webhooks registered by reseller.
     */
    public function index(Request $request)
    {
        $webhooks = Webhook::where('user_id', $request->user()->id)
            ->withCount('logs')
            ->latest()
            ->get();

        $availableEvents = [
            'domain.registered'     => 'Domain Registered',
            'domain.renewed'        => 'Domain Renewed',
            'domain.transferred'    => 'Domain Transferred',
            'domain.expiring_soon'  => 'Domain Expiring Soon (30/7/1 days)',
            'domain.expired'        => 'Domain Expired',
            'domain.nameservers_updated' => 'Nameservers Updated',
            'invoice.created'       => 'Invoice Generated',
            'invoice.paid'          => 'Invoice Paid',
            'wallet.balance_low'    => 'Low Balance Warning',
        ];

        return view('reseller.webhooks.index', compact('webhooks', 'availableEvents'));
    }

    /**
     * Register new webhook endpoint.
     */
    public function store(Request $request)
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

        return back()->with('success', 'Webhook endpoint registered successfully.');
    }

    /**
     * Update webhook configuration.
     */
    public function update(Request $request, $id)
    {
        $webhook = Webhook::where('user_id', $request->user()->id)->findOrFail($id);

        $request->validate([
            'target_url'        => ['required', 'url', 'max:255'],
            'subscribed_events' => ['required', 'array', 'min:1'],
        ]);

        $webhook->update([
            'target_url'        => $request->input('target_url'),
            'subscribed_events' => $request->input('subscribed_events'),
            'is_active'         => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Webhook endpoint updated.');
    }

    /**
     * Delete webhook endpoint.
     */
    public function destroy(Request $request, $id)
    {
        $webhook = Webhook::where('user_id', $request->user()->id)->findOrFail($id);
        $webhook->delete();

        return back()->with('success', 'Webhook endpoint removed.');
    }

    /**
     * Send test ping event to webhook endpoint.
     */
    public function test(Request $request, $id)
    {
        $webhook = Webhook::where('user_id', $request->user()->id)->findOrFail($id);

        $testPayload = [
            'event'     => 'domain.test_ping',
            'timestamp' => now()->toIso8601String(),
            'domain'    => 'test-example.com',
            'message'   => 'This is an automated test ping from DomainReseller.',
        ];

        DispatchWebhookJob::dispatch($webhook->id, 'domain.test_ping', $testPayload);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Test ping queued for delivery.']);
        }

        return back()->with('success', 'Test ping queued for delivery.');
    }
}
