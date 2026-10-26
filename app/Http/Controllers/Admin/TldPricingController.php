<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrarModule;
use App\Models\TldPricing;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TldPricingController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * TLD pricing matrix with retail and reseller margins.
     */
    public function index()
    {
        $tlds = TldPricing::with('registrar')->orderBy('tld')->get();
        return view('admin.tlds.index', compact('tlds'));
    }

    /**
     * Form to add a new TLD extension.
     */
    public function create()
    {
        $registrars = RegistrarModule::where('is_active', true)->get();
        return view('admin.tlds.create', compact('registrars'));
    }

    /**
     * Store newly configured TLD.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tld'                       => ['required', 'string', 'max:20', 'unique:tld_pricing,tld'],
            'registrar_id'              => ['required', 'exists:registrar_modules,id'],
            'cost_register'             => ['required', 'numeric', 'min:0'],
            'cost_renew'                => ['required', 'numeric', 'min:0'],
            'cost_transfer'             => ['required', 'numeric', 'min:0'],
            'client_register'           => ['required', 'numeric', 'min:0'],
            'client_renew'              => ['required', 'numeric', 'min:0'],
            'client_transfer'           => ['required', 'numeric', 'min:0'],
            'default_reseller_register' => ['required', 'numeric', 'min:0'],
            'default_reseller_renew'    => ['required', 'numeric', 'min:0'],
            'default_reseller_transfer' => ['required', 'numeric', 'min:0'],
            'min_years'                 => ['required', 'integer', 'min:1'],
            'max_years'                 => ['required', 'integer', 'max:10'],
            'is_active'                 => ['nullable', 'boolean'],
            'dns_management_available'  => ['nullable', 'boolean'],
            'email_forwarding_available'=> ['nullable', 'boolean'],
            'id_protection_available'   => ['nullable', 'boolean'],
            'id_protection_cost'        => ['nullable', 'numeric', 'min:0'],
            'epp_code_required'         => ['nullable', 'boolean'],
            'grace_period_days'         => ['nullable', 'integer', 'min:0'],
            'redemption_period_days'    => ['nullable', 'integer', 'min:0'],
            'redemption_fee'            => ['nullable', 'numeric', 'min:0'],
        ]);

        $tld = strtolower(trim($validated['tld']));
        if (!str_starts_with($tld, '.')) {
            $tld = '.' . $tld;
        }
        $validated['tld'] = $tld;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['dns_management_available'] = $request->boolean('dns_management_available', true);
        $validated['email_forwarding_available'] = $request->boolean('email_forwarding_available', true);
        $validated['id_protection_available'] = $request->boolean('id_protection_available', true);
        $validated['epp_code_required'] = $request->boolean('epp_code_required', true);

        $tldModel = TldPricing::create($validated);

        $this->activityLogger->log('tld_created', "Admin added TLD extension {$tldModel->tld}", $tldModel);

        return redirect()->route('admin.tlds.index')->with('success', "TLD {$tldModel->tld} configured successfully.");
    }

    /**
     * Edit form for TLD extension.
     */
    public function edit($id)
    {
        $tld = TldPricing::findOrFail($id);
        $registrars = RegistrarModule::where('is_active', true)->get();
        return view('admin.tlds.edit', compact('tld', 'registrars'));
    }

    /**
     * Update TLD pricing and policies.
     */
    public function update(Request $request, $id)
    {
        $tldModel = TldPricing::findOrFail($id);

        $validated = $request->validate([
            'tld'                       => ['required', 'string', 'max:20', Rule::unique('tld_pricing', 'tld')->ignore($tldModel->id)],
            'registrar_id'              => ['required', 'exists:registrar_modules,id'],
            'cost_register'             => ['required', 'numeric', 'min:0'],
            'cost_renew'                => ['required', 'numeric', 'min:0'],
            'cost_transfer'             => ['required', 'numeric', 'min:0'],
            'client_register'           => ['required', 'numeric', 'min:0'],
            'client_renew'              => ['required', 'numeric', 'min:0'],
            'client_transfer'           => ['required', 'numeric', 'min:0'],
            'default_reseller_register' => ['required', 'numeric', 'min:0'],
            'default_reseller_renew'    => ['required', 'numeric', 'min:0'],
            'default_reseller_transfer' => ['required', 'numeric', 'min:0'],
            'min_years'                 => ['required', 'integer', 'min:1'],
            'max_years'                 => ['required', 'integer', 'max:10'],
            'id_protection_cost'        => ['nullable', 'numeric', 'min:0'],
            'grace_period_days'         => ['nullable', 'integer', 'min:0'],
            'redemption_period_days'    => ['nullable', 'integer', 'min:0'],
            'redemption_fee'            => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['dns_management_available'] = $request->boolean('dns_management_available');
        $validated['email_forwarding_available'] = $request->boolean('email_forwarding_available');
        $validated['id_protection_available'] = $request->boolean('id_protection_available');
        $validated['epp_code_required'] = $request->boolean('epp_code_required');

        $tldModel->update($validated);

        $this->activityLogger->log('tld_updated', "Admin updated TLD extension {$tldModel->tld}", $tldModel);

        return redirect()->route('admin.tlds.index')->with('success', "TLD {$tldModel->tld} updated successfully.");
    }

    /**
     * Delete TLD.
     */
    public function destroy($id)
    {
        $tldModel = TldPricing::findOrFail($id);
        $tld = $tldModel->tld;
        $tldModel->delete();

        $this->activityLogger->log('tld_deleted', "Admin removed TLD extension {$tld}");

        return redirect()->route('admin.tlds.index')->with('success', "TLD {$tld} removed.");
    }

    /**
     * Bulk update TLD prices by fixed or percentage margin.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'adjustment_type' => ['required', 'in:percentage,fixed'],
            'margin'          => ['required', 'numeric'],
            'target'          => ['required', 'in:client,reseller,both'],
        ]);

        $margin = (float) $request->input('margin');
        $type = $request->input('adjustment_type');
        $target = $request->input('target');

        $tlds = TldPricing::all();
        foreach ($tlds as $tld) {
            if ($target === 'client' || $target === 'both') {
                if ($type === 'percentage') {
                    $tld->client_register = round($tld->cost_register * (1 + $margin / 100), 2);
                    $tld->client_renew = round($tld->cost_renew * (1 + $margin / 100), 2);
                    $tld->client_transfer = round($tld->cost_transfer * (1 + $margin / 100), 2);
                } else {
                    $tld->client_register = round($tld->cost_register + $margin, 2);
                    $tld->client_renew = round($tld->cost_renew + $margin, 2);
                    $tld->client_transfer = round($tld->cost_transfer + $margin, 2);
                }
            }

            if ($target === 'reseller' || $target === 'both') {
                $resellerMargin = $type === 'percentage' ? ($margin * 0.75) : ($margin * 0.75);
                if ($type === 'percentage') {
                    $tld->default_reseller_register = round($tld->cost_register * (1 + $resellerMargin / 100), 2);
                    $tld->default_reseller_renew = round($tld->cost_renew * (1 + $resellerMargin / 100), 2);
                    $tld->default_reseller_transfer = round($tld->cost_transfer * (1 + $resellerMargin / 100), 2);
                } else {
                    $tld->default_reseller_register = round($tld->cost_register + $resellerMargin, 2);
                    $tld->default_reseller_renew = round($tld->cost_renew + $resellerMargin, 2);
                    $tld->default_reseller_transfer = round($tld->cost_transfer + $resellerMargin, 2);
                }
            }

            $tld->save();
        }

        $this->activityLogger->log('tld_bulk_updated', "Admin performed bulk price update of {$margin} ({$type}) on {$target}");

        return redirect()->route('admin.tlds.index')->with('success', 'Bulk TLD pricing updated successfully.');
    }
}
