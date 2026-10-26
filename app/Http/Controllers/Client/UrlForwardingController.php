<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\UrlForward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UrlForwardingController extends Controller
{
    public function index(Domain $domain)
    {
        $this->authorizeDomain($domain);
        $forwards = $domain->urlForwards;

        return view('client.domains.url-forwarding', compact('domain', 'forwards'));
    }

    public function store(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $request->validate([
            'source_subdomain' => 'nullable|string|max:100',
            'destination_url' => 'required|url',
            'redirect_type' => 'required|in:301,302,masked',
            'page_title' => 'nullable|string|max:150',
        ]);

        $domain->urlForwards()->create([
            'source_subdomain' => $request->source_subdomain ?: '@',
            'destination_url' => $request->destination_url,
            'redirect_type' => $request->redirect_type,
            'page_title' => $request->page_title,
            'is_active' => true,
        ]);

        return back()->with('success', 'URL redirection configured.');
    }

    public function destroy(Domain $domain, int $id)
    {
        $this->authorizeDomain($domain);
        $forward = $domain->urlForwards()->findOrFail($id);
        $forward->delete();

        return back()->with('success', 'URL redirect removed.');
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
