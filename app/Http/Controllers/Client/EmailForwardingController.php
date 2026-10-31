<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\EmailForward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class EmailForwardingController extends Controller
{
    public function index(Domain $domain)
    {
        $this->authorizeDomain($domain);
        $forwarders = $domain->emailForwards;

        // Check if MX records are pointed to cluster
        $mxConfigured = false;
        try {
            $mxRecords = @dns_get_record($domain->domain_name, DNS_MX);
            if (!empty($mxRecords)) {
                foreach ($mxRecords as $mx) {
                    $target = strtolower($mx['target'] ?? '');
                    if (str_contains($target, 'hostnibo.com') || str_contains($target, '188.40.147.141')) {
                        $mxConfigured = true;
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {}

        return view('client.domains.email-forwarding', compact('domain', 'forwarders', 'mxConfigured'));
    }

    public function store(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $request->validate([
            'source_prefix' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-\.\*]+$/'],
            'destination_email' => ['required', 'email', 'max:191'],
        ]);

        $domain->emailForwards()->create([
            'source_prefix' => strtolower($request->source_prefix),
            'destination_email' => strtolower($request->destination_email),
            'is_active' => true,
        ]);

        $this->syncEximForwarders($domain->domain_name);

        return back()->with('success', 'Email forwarder created successfully.');
    }

    public function destroy(Domain $domain, int $id)
    {
        $this->authorizeDomain($domain);
        $forward = $domain->emailForwards()->findOrFail($id);
        $forward->delete();

        $this->syncEximForwarders($domain->domain_name);

        return back()->with('success', 'Email forwarder removed.');
    }

    protected function syncEximForwarders(string $domainName): void
    {
        try {
            Http::timeout(3)->get('http://127.0.0.1:9099/sync', [
                'domain' => $domainName,
            ]);
        } catch (\Throwable $e) {
            // Daemon timeout or local fallback
        }
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
