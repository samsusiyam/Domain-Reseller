<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\Domain\DomainManagementService;
use App\Services\Domain\DomainOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDomainController extends Controller
{
    public function __construct(
        protected DomainManagementService $managementService,
        protected DomainOrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $query = Auth::user()->domains()->with(['registrar']);

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->where('domain_name', 'like', '%' . $request->search . '%');
        }

        $domains = $query->latest()->paginate(15)->withQueryString();

        return view('client.domains.index', compact('domains'));
    }

    public function show(Domain $domain)
    {
        $this->authorizeDomain($domain);

        $domain->load(['registrar', 'dnsRecords', 'childNameservers', 'emailForwards', 'urlForwards']);

        return view('client.domains.show', compact('domain'));
    }

    public function toggleLock(Domain $domain)
    {
        $this->authorizeDomain($domain);

        $newLock = !$domain->is_locked;
        $this->managementService->toggleLock($domain, $newLock);

        return back()->with('success', 'Domain lock status updated successfully.');
    }

    public function togglePrivacy(Domain $domain)
    {
        $this->authorizeDomain($domain);

        $newPrivacy = !$domain->is_private;
        $this->managementService->toggleIdProtection($domain, $newPrivacy);

        return back()->with('success', 'WHOIS ID Privacy status updated successfully.');
    }

    public function toggleAutoRenew(Domain $domain)
    {
        $this->authorizeDomain($domain);

        $domain->update(['is_auto_renew' => !$domain->is_auto_renew]);

        return back()->with('success', 'Auto-renew setting updated.');
    }

    public function fetchEppCode(Domain $domain)
    {
        $this->authorizeDomain($domain);

        $authCode = $this->managementService->getAuthCode($domain);

        return back()->with('auth_code', $authCode)->with('success', 'EPP Transfer Authorization Code retrieved.');
    }

    public function renew(Request $request, Domain $domain)
    {
        $this->authorizeDomain($request, $domain);
        $request->validate(['years' => 'required|integer|min:1|max:10']);

        $res = $this->orderService->createOrder(
            Auth::user(),
            [[
                'domain' => $domain->domain_name,
                'action' => 'renew',
                'years' => (int) $request->years,
                'domain_id' => $domain->id,
            ]]
        );

        return redirect()->route('client.invoices.show', $res['invoice']->id)->with('success', 'Renewal order created. Please pay the invoice.');
    }

    protected function authorizeDomain(Domain $domain): void
    {
        if ($domain->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this domain.');
        }
    }
}
