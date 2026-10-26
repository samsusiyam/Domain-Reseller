<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\EmailForward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailForwardingController extends Controller
{
    public function index(Domain $domain)
    {
        $this->authorizeDomain($domain);
        $forwarders = $domain->emailForwards;

        return view('client.domains.email-forwarding', compact('domain', 'forwarders'));
    }

    public function store(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $request->validate([
            'source_prefix' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'destination_email' => ['required', 'email', 'max:191'],
        ]);

        $domain->emailForwards()->create([
            'source_prefix' => strtolower($request->source_prefix),
            'destination_email' => strtolower($request->destination_email),
            'is_active' => true,
        ]);

        return back()->with('success', 'Email forwarder created successfully.');
    }

    public function destroy(Domain $domain, int $id)
    {
        $this->authorizeDomain($domain);
        $forward = $domain->emailForwards()->findOrFail($id);
        $forward->delete();

        return back()->with('success', 'Email forwarder removed.');
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
