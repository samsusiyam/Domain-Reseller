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
            if ($request->status === 'expiring') {
                $query->where('status', 'active')
                      ->whereNotNull('expires_at')
                      ->where('expires_at', '<=', now()->addDays(30))
                      ->where('expires_at', '>=', now());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->where('domain_name', 'like', '%' . $request->search . '%');
        }

        $domains = $query->latest()->paginate(15)->withQueryString();

        return view('client.domains.index', compact('domains'));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'domain_ids' => 'required|array|min:1',
            'domain_ids.*' => 'integer',
            'action' => 'required|string|in:lock,unlock,enable_autorenew,disable_autorenew,change_nameservers',
            'nameservers' => 'nullable|array',
            'nameservers.*' => 'nullable|string|max:191',
        ]);

        $domains = Auth::user()->domains()->whereIn('id', $request->domain_ids)->get();

        if ($domains->isEmpty()) {
            return back()->with('error', 'No valid domains selected.');
        }

        $successCount = 0;
        $failCount = 0;

        switch ($request->action) {
            case 'lock':
                foreach ($domains as $domain) {
                    if ($this->managementService->toggleLock($domain, true)) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
                $msg = "Registrar lock enabled on {$successCount} domain(s).";
                break;

            case 'unlock':
                foreach ($domains as $domain) {
                    if ($this->managementService->toggleLock($domain, false)) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
                $msg = "Registrar lock disabled on {$successCount} domain(s).";
                break;

            case 'enable_autorenew':
                $updated = Auth::user()->domains()->whereIn('id', $request->domain_ids)->update(['is_auto_renew' => true]);
                $msg = "Auto-renew enabled for {$updated} domain(s).";
                break;

            case 'disable_autorenew':
                $updated = Auth::user()->domains()->whereIn('id', $request->domain_ids)->update(['is_auto_renew' => false]);
                $msg = "Auto-renew disabled for {$updated} domain(s).";
                break;

            case 'change_nameservers':
                $ns = array_values(array_filter(array_map('trim', $request->nameservers ?? [])));
                if (count($ns) < 2) {
                    return back()->with('error', 'Please provide at least two valid nameservers.');
                }
                foreach ($domains as $domain) {
                    if ($this->managementService->updateNameservers($domain, $ns)) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
                $msg = "Nameservers updated on {$successCount} domain(s).";
                break;
        }

        if ($failCount > 0) {
            return back()->with('warning', "{$msg} ({$failCount} domain(s) failed at registrar).");
        }

        return back()->with('success', $msg);
    }

    public function show(Domain $domain)
    {
        $this->authorizeDomain($domain);

        $domain->load(['registrar', 'dnsRecords', 'childNameservers', 'emailForwards', 'urlForwards']);

        return view('client.domains.show', compact('domain'));
    }

    public function toggleLock(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $newLock = !$domain->is_locked;
        $success = $this->managementService->toggleLock($domain, $newLock);

        if (!$success) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Failed to toggle lock at registrar.'], 422);
            }
            return back()->with('error', 'Failed to toggle lock at registrar.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'is_locked' => $newLock]);
        }

        return back()->with('success', 'Domain lock status updated successfully.');
    }

    public function togglePrivacy(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $newPrivacy = !$domain->is_private;
        $success = $this->managementService->toggleIdProtection($domain, $newPrivacy);

        if (!$success) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Failed to toggle WHOIS ID privacy at registrar.'], 422);
            }
            return back()->with('error', 'Failed to toggle WHOIS ID privacy at registrar.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'is_private' => $newPrivacy]);
        }

        return back()->with('success', 'WHOIS ID Privacy status updated successfully.');
    }

    public function toggleAutoRenew(Domain $domain)
    {
        $this->authorizeDomain($domain);

        $domain->update(['is_auto_renew' => !$domain->is_auto_renew]);

        return back()->with('success', 'Auto-renew setting updated.');
    }

    public function fetchEppCode(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        try {
            $authCode = $this->managementService->getAuthCode($domain);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'epp_code' => $authCode]);
            }

            return back()->with('auth_code', $authCode)->with('success', 'EPP Transfer Authorization Code retrieved.');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function renew(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);
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

    /**
     * Show Internal Domain Push form.
     */
    public function showPush(Domain $domain)
    {
        $this->authorizeDomain($domain);

        return view('client.domains.push', compact('domain'));
    }

    /**
     * Process instant Internal Domain Push to another user.
     */
    public function processPush(Request $request, Domain $domain)
    {
        $this->authorizeDomain($domain);

        $request->validate([
            'recipient_email' => ['required', 'email'],
            'confirm_transfer' => ['accepted'],
        ]);

        $recipientEmail = strtolower(trim($request->input('recipient_email')));

        if ($recipientEmail === strtolower(Auth::user()->email)) {
            return back()->with('error', 'You cannot push a domain to your own account.');
        }

        $recipient = \App\Models\User::where('email', $recipientEmail)->first();
        if (!$recipient) {
            return back()->with('error', "No client or reseller account found with email '{$recipientEmail}'. The recipient must create a free HostNibo account first.");
        }

        if ($domain->is_locked) {
            return back()->with('error', 'Domain is currently locked. Please unlock the domain before pushing it to another user.');
        }

        if ($domain->status !== 'active') {
            return back()->with('error', "Only active domains can be pushed. Current status: {$domain->status}");
        }

        $sender = Auth::user();
        $domainName = $domain->domain_name;

        // Perform instant ownership transfer
        $domain->update([
            'user_id' => $recipient->id,
            'is_auto_renew' => false,
        ]);

        // Log activity for both accounts
        try {
            \App\Models\ActivityLog::create([
                'user_id' => $sender->id,
                'log_name' => 'domain_push',
                'description' => "Transferred domain {$domainName} internally to {$recipient->name} ({$recipient->email})",
                'ip_address' => $request->ip(),
            ]);

            \App\Models\ActivityLog::create([
                'user_id' => $recipient->id,
                'log_name' => 'domain_push_received',
                'description' => "Received domain {$domainName} from {$sender->name} ({$sender->email}) via instant internal push",
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('client.domains.index')
            ->with('success', "⚡ Instant Push Success: Domain {$domainName} has been immediately transferred to {$recipient->name} ({$recipient->email}) without any fees!");
    }

}
