<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * List all API keys for authenticated reseller.
     */
    public function index(Request $request)
    {
        $apiKeys = ApiKey::where('user_id', $request->user()->id)->latest()->get();
        return view('reseller.api-keys.index', compact('apiKeys'));
    }

    /**
     * Generate new API Key & Secret.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key_name'     => ['required', 'string', 'max:100'],
            'ip_whitelist' => ['nullable', 'string'],
        ]);

        $ips = null;
        if ($request->filled('ip_whitelist')) {
            $ips = array_map('trim', explode("\n", str_replace("\r", "", $request->input('ip_whitelist'))));
            $ips = array_values(array_filter($ips));
        }

        $reseller = $request->user();
        $generated = ApiKey::generateForUser($reseller, $request->input('key_name'), $ips);

        return redirect()->route('reseller.api-keys.index')
            ->with('success', 'API Key generated successfully.')
            ->with('new_api_key', $generated['public_key'])
            ->with('new_api_secret', $generated['secret_key']);
    }

    /**
     * Update API key label and IP whitelist.
     */
    public function update(Request $request, $id)
    {
        $apiKey = ApiKey::where('user_id', $request->user()->id)->findOrFail($id);

        $request->validate([
            'key_name'     => ['required', 'string', 'max:100'],
            'ip_whitelist' => ['nullable', 'string'],
            'status'       => ['required', 'in:active,inactive'],
        ]);

        $ips = null;
        if ($request->filled('ip_whitelist')) {
            $ips = array_map('trim', explode("\n", str_replace("\r", "", $request->input('ip_whitelist'))));
            $ips = array_values(array_filter($ips));
        }

        $apiKey->update([
            'key_name'     => $request->input('key_name'),
            'ip_whitelist' => $ips,
            'status'       => $request->input('status'),
        ]);

        return back()->with('success', 'API Key updated successfully.');
    }

    /**
     * Revoke and delete API Key.
     */
    public function destroy(Request $request, $id)
    {
        $apiKey = ApiKey::where('user_id', $request->user()->id)->findOrFail($id);
        $apiKey->delete();

        return back()->with('success', 'API Key revoked and deleted.');
    }

    /**
     * Regenerate API Secret.
     */
    public function regenerateSecret(Request $request, $id)
    {
        $apiKey = ApiKey::where('user_id', $request->user()->id)->findOrFail($id);
        $rawSecret = 'sk_' . Str::random(40);

        $apiKey->update(['api_secret' => $rawSecret]);

        return back()
            ->with('success', 'API Secret regenerated successfully.')
            ->with('new_api_secret', $rawSecret);
    }

    /**
     * View API access logs.
     */
    public function logs(Request $request)
    {
        $query = ActivityLog::where('user_id', $request->user()->id)
            ->where('log_name', 'api_request');

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->input('search') . '%');
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();

        return view('reseller.api-keys.logs', compact('logs'));
    }

    /**
     * Interactive API Documentation and WHMCS Module Integration Guide.
     */
    public function docs()
    {
        return view('reseller.api-keys.docs');
    }
}
