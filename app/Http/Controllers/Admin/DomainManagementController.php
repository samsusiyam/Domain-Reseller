<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\RegistrarModule;
use App\Models\User;
use App\Services\Audit\ActivityLogger;
use App\Services\Domain\DomainManagementService;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Http\Request;

class DomainManagementController extends Controller
{
    public function __construct(
        protected DomainManagementService $managementService,
        protected RegistrarManager $registrarManager,
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Master domain directory with search and status filters.
     */
    public function index(Request $request)
    {
        $query = Domain::with('user', 'registrar');

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('domain_name', 'like', $search)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', $search)->orWhere('email', 'like', $search);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('registrar_id')) {
            $query->where('registrar_id', $request->input('registrar_id'));
        }

        $domains = $query->latest()->paginate(15)->withQueryString();
        $registrars = RegistrarModule::where('is_active', true)->get();

        return view('admin.domains.index', compact('domains', 'registrars'));
    }

    /**
     * Full domain details, nameservers, registrar info, and logs.
     */
    public function show($id)
    {
        $domain = Domain::with('user', 'registrar', 'dnsRecords', 'childNameservers')->findOrFail($id);
        return view('admin.domains.show', compact('domain'));
    }

    /**
     * Form to edit administrative attributes of domain.
     */
    public function edit($id)
    {
        $domain = Domain::with('user', 'registrar')->findOrFail($id);
        $registrars = RegistrarModule::where('is_active', true)->get();
        return view('admin.domains.edit', compact('domain', 'registrars'));
    }

    /**
     * Update administrative overrides.
     */
    public function update(Request $request, $id)
    {
        $domain = Domain::findOrFail($id);

        $validated = $request->validate([
            'status'            => ['required', 'in:active,expired,pending_transfer,cancelled,fraud'],
            'registrar_id'      => ['required', 'exists:registrar_modules,id'],
            'registration_date' => ['required', 'date'],
            'expires_at'        => ['required', 'date', 'after:registration_date'],
            'is_locked'         => ['required', 'boolean'],
            'is_private'        => ['required', 'boolean'],
            'auto_renew'        => ['required', 'boolean'],
            'auth_code'         => ['nullable', 'string', 'max:255'],
        ]);

        $validated['registered_at'] = $validated['registration_date'];
        $validated['is_auto_renew'] = $validated['auto_renew'];
        $domain->update($validated);

        $this->activityLogger->log(
            'admin_domain_updated',
            "Admin updated domain #{$domain->id} ({$domain->domain_name})",
            $domain,
            $validated
        );

        return redirect()->route('admin.domains.show', $domain->id)->with('success', 'Domain updated successfully.');
    }

    /**
     * Sync domain details with upstream registrar.
     */
    public function syncWithRegistrar($id)
    {
        $domain = Domain::with('registrar')->findOrFail($id);

        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $details = $registrar->getDomainDetails($domain->domain_name);

            $updateData = [
                'status'     => $details->status,
                'is_locked'  => $details->isLocked,
                'is_private' => $details->isPrivate,
                'last_sync_at' => now(),
            ];

            if ($details->createdAt) {
                $updateData['registered_at'] = $details->createdAt;
            }
            if ($details->expiresAt) {
                $updateData['expires_at'] = $details->expiresAt;
            }
            if (!empty($details->nameservers)) {
                $updateData['nameservers'] = $details->nameservers;
            }

            $domain->update($updateData);

            return back()->with('success', 'Domain synchronized with registrar successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Registrar sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Force administrative domain renewal.
     */
    public function renew(Request $request, $id)
    {
        $domain = Domain::with('registrar')->findOrFail($id);
        $years = (int) $request->input('years', 1);

        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $renewalDto = new \App\DTOs\DomainRenewalDTO(
                domainName: $domain->domain_name,
                years: $years,
                currentExpiry: $domain->expires_at?->format('Y-m-d')
            );
            $result = $registrar->renewDomain($renewalDto);

            if ($result->success) {
                $newExpiry = $domain->expires_at ? $domain->expires_at->copy()->addYears($years) : now()->addYears($years);
                $domain->update([
                    'expires_at' => $result->newExpiryDate ? \Carbon\Carbon::parse($result->newExpiryDate) : $newExpiry,
                    'status'     => 'active',
                ]);

                $this->activityLogger->log('admin_domain_renewed', "Admin forced renewal for {$domain->domain_name} for {$years} yr(s)");
                return back()->with('success', "Domain renewed successfully for {$years} year(s).");
            }

            return back()->with('error', 'Renewal failed: ' . $result->message);
        } catch (\Throwable $e) {
            return back()->with('error', 'Registrar error: ' . $e->getMessage());
        }
    }

    /**
     * Toggle domain transfer lock at registrar.
     */
    public function toggleLock($id)
    {
        $domain = Domain::with('registrar')->findOrFail($id);
        $newLock = !$domain->is_locked;

        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $success = $newLock ? $registrar->lockDomain($domain->domain_name) : $registrar->unlockDomain($domain->domain_name);

            if ($success) {
                $domain->update(['is_locked' => $newLock]);
                return back()->with('success', 'Domain lock status updated.');
            }
            return back()->with('error', 'Failed to update lock status at registrar.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Registrar error: ' . $e->getMessage());
        }
    }

    /**
     * Toggle WHOIS privacy at registrar.
     */
    public function togglePrivacy($id)
    {
        $domain = Domain::with('registrar')->findOrFail($id);
        $newPrivacy = !$domain->is_private;

        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $success = $registrar->toggleIdProtection($domain->domain_name, $newPrivacy);

            if ($success) {
                $domain->update(['is_private' => $newPrivacy]);
                return back()->with('success', 'WHOIS privacy updated.');
            }
            return back()->with('error', 'Failed to toggle privacy at registrar.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Registrar error: ' . $e->getMessage());
        }
    }

    /**
     * Fetch EPP / transfer authorization code.
     */
    public function fetchEppCode($id)
    {
        $domain = Domain::with('registrar')->findOrFail($id);

        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $authCode = $registrar->getAuthCode($domain->domain_name);

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'epp_code' => $authCode]);
            }
            return back()->with('info', "EPP Code for {$domain->domain_name}: {$authCode}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to fetch EPP code: ' . $e->getMessage());
        }
    }

    /**
     * Update authoritative nameservers.
     */
    public function updateNameservers(Request $request, $id)
    {
        $domain = Domain::with('registrar')->findOrFail($id);

        $request->validate([
            'ns1' => ['required', 'string', 'max:255'],
            'ns2' => ['required', 'string', 'max:255'],
            'ns3' => ['nullable', 'string', 'max:255'],
            'ns4' => ['nullable', 'string', 'max:255'],
        ]);

        $nameservers = array_values(array_filter([
            $request->input('ns1'),
            $request->input('ns2'),
            $request->input('ns3'),
            $request->input('ns4'),
        ]));

        $success = $this->managementService->updateNameservers($domain, $nameservers);

        if ($success) {
            return back()->with('success', 'Authoritative nameservers updated successfully.');
        }

        return back()->with('error', 'Failed to update nameservers at registrar.');
    }

    /**
     * Delete domain record from local database.
     */
    public function destroy($id)
    {
        $domain = Domain::findOrFail($id);
        $this->activityLogger->log('admin_domain_deleted', "Admin deleted domain record #{$domain->id} ({$domain->domain_name})");
        $domain->delete();

        return redirect()->route('admin.domains.index')->with('success', 'Domain record deleted.');
    }
}
