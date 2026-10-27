<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApiKey;
use App\Models\Transaction;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ResellerManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Reseller directory with search and tier filters.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'reseller')
            ->withCount('domains', 'apiKeys');

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('company_name', 'like', $search);
            });
        }

        if ($request->filled('tier')) {
            $query->where('pricing_tier', $request->input('tier'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $resellers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.resellers.index', compact('resellers'));
    }

    /**
     * Show form to create new reseller.
     */
    public function create()
    {
        return view('admin.resellers.create');
    }

    /**
     * Store new reseller account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'     => ['required', 'string', 'min:8'],
            'company_name' => ['required', 'string', 'max:255'],
            'pricing_tier' => ['required', 'in:standard,silver,gold,platinum'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'country'      => ['nullable', 'string', 'max:2'],
        ]);

        $reseller = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => 'reseller',
            'company_name'      => $validated['company_name'],
            'pricing_tier'      => $validated['pricing_tier'],
            'phone'             => $validated['phone'] ?? null,
            'country'           => $validated['country'] ?? 'US',
            'status'            => 'active',
            'email_verified_at' => now(),
            'balance' => 0.0000,
        ]);

        $this->activityLogger->log(
            'reseller_created',
            "Admin created reseller #{$reseller->id} ({$reseller->email}) on tier {$reseller->pricing_tier}",
            $reseller
        );

        return redirect()->route('admin.resellers.show', $reseller->id)->with('success', 'Reseller created successfully.');
    }

    /**
     * Show reseller profile, metrics, API keys, and domains.
     */
    public function show($id)
    {
        $reseller = User::where('role', 'reseller')
            ->withCount('domains', 'apiKeys', 'orders')
            ->findOrFail($id);

        $domains = $reseller->domains()->latest()->take(10)->get();
        $apiKeys = $reseller->apiKeys()->latest()->get();
        $transactions = $reseller->transactions()->latest()->take(10)->get();
        $orders = $reseller->orders()->latest()->take(10)->get();

        return view('admin.resellers.show', compact('reseller', 'domains', 'apiKeys', 'transactions', 'orders'));
    }

    /**
     * Show form to edit reseller.
     */
    public function edit($id)
    {
        $reseller = User::where('role', 'reseller')->findOrFail($id);
        return view('admin.resellers.edit', compact('reseller'));
    }

    /**
     * Update reseller profile and tier.
     */
    public function update(Request $request, $id)
    {
        $reseller = User::where('role', 'reseller')->findOrFail($id);

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($reseller->id)],
            'company_name' => ['required', 'string', 'max:255'],
            'pricing_tier' => ['required', 'in:standard,silver,gold,platinum'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'status'       => ['required', 'in:active,suspended,pending'],
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['min:8']]);
            $validated['password'] = Hash::make($request->input('password'));
        }

        $reseller->update($validated);

        $this->activityLogger->log(
            'reseller_updated',
            "Admin updated reseller #{$reseller->id} ({$reseller->email})",
            $reseller,
            $validated
        );

        return redirect()->route('admin.resellers.show', $reseller->id)->with('success', 'Reseller details updated.');
    }

    /**
     * Terminate reseller account.
     */
    public function destroy($id)
    {
        $reseller = User::where('role', 'reseller')->findOrFail($id);

        if ($reseller->domains()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete reseller with active domain registrations.');
        }

        $this->activityLogger->log('reseller_deleted', "Admin deleted reseller #{$reseller->id} ({$reseller->email})");
        $reseller->delete();

        return redirect()->route('admin.resellers.index')->with('success', 'Reseller account deleted.');
    }

    /**
     * Toggle status between active and suspended.
     */
    public function toggleStatus($id)
    {
        $reseller = User::where('role', 'reseller')->findOrFail($id);
        $newStatus = $reseller->status === 'active' ? 'suspended' : 'active';
        $reseller->update(['status' => $newStatus]);

        $this->activityLogger->log('reseller_status_toggled', "Admin toggled reseller #{$reseller->id} to {$newStatus}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'status' => $newStatus]);
        }

        return back()->with('success', "Reseller status changed to {$newStatus}.");
    }

    /**
     * Update reseller pricing tier.
     */
    public function updateTier(Request $request, $id)
    {
        $reseller = User::where('role', 'reseller')->findOrFail($id);

        $request->validate([
            'pricing_tier' => ['required', 'in:standard,silver,gold,platinum'],
        ]);

        $reseller->update(['pricing_tier' => $request->input('pricing_tier')]);

        $this->activityLogger->log(
            'reseller_tier_updated',
            "Admin set reseller #{$reseller->id} tier to {$request->input('pricing_tier')}",
            $reseller
        );

        return back()->with('success', 'Pricing tier updated successfully.');
    }

    /**
     * Adjust wholesale balance.
     */
    public function adjustBalance(Request $request, $id)
    {
        $reseller = User::where('role', 'reseller')->findOrFail($id);

        $request->validate([
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'type'        => ['required', 'in:credit,debit'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $amount = (float) $request->input('amount');
        $type = $request->input('type');
        $currentBalance = (float) $reseller->wallet_balance;

        if ($type === 'debit' && $currentBalance < $amount) {
            return back()->with('error', 'Reseller has insufficient balance for this debit.');
        }

        $newBalance = $type === 'credit' ? $currentBalance + $amount : $currentBalance - $amount;
        if ($type === 'credit') $reseller->creditBalance($amount); else $reseller->deductBalance($amount);

        Transaction::create([
            'user_id'        => $reseller->id,
            'type'           => $type,
            'amount'         => $amount,
            'currency'       => 'USD',
            'exchange_rate'  => 1.000000,
            'gateway' => 'manual', 'amount_in_usd' => $amount,
            'reference_id'   => 'RES-ADJ-' . strtoupper(uniqid()),
            'status'         => 'completed',
            'description'    => $request->input('description') . ' (Wholesale Credit Adjusted by Admin)',
        ]);

        $this->activityLogger->log(
            'reseller_balance_adjusted',
            "Admin {$type}ed \${$amount} to reseller #{$reseller->id}. New balance: \${$newBalance}",
            $reseller
        );

        return back()->with('success', "Reseller balance {$type}ed successfully. New balance: \${$newBalance}");
    }

    /**
     * List all API keys generated by reseller.
     */
    public function apiKeys($id)
    {
        $reseller = User::where('role', 'reseller')->findOrFail($id);
        $apiKeys = $reseller->apiKeys()->latest()->get();

        return view('admin.resellers.api-keys', compact('reseller', 'apiKeys'));
    }

    /**
     * Revoke specific API key.
     */
    public function revokeApiKey($resellerId, $keyId)
    {
        $reseller = User::where('role', 'reseller')->findOrFail($resellerId);
        $apiKey = ApiKey::where('user_id', $reseller->id)->findOrFail($keyId);
        $apiKey->delete();

        $this->activityLogger->log('api_key_revoked_by_admin', "Admin revoked API key {$apiKey->key_identifier} for reseller #{$reseller->id}");

        return back()->with('success', 'API key revoked successfully.');
    }
}
