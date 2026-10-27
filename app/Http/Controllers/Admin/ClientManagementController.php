<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Display paginated clients list with filters.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'client')->withCount('domains');

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('phone', 'like', $search)
                  ->orWhere('company_name', 'like', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $clients = $query->latest()->paginate(15)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Show form to create new client.
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Store new client.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'     => ['required', 'string', 'min:8'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address_1'    => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:100'],
            'state'        => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:2'],
            'postal_code'  => ['nullable', 'string', 'max:20'],
            'currency_id'  => ['nullable', 'exists:currencies,id'],
        ]);

        $client = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => 'client',
            'phone'             => $validated['phone'] ?? null,
            'company_name'      => $validated['company_name'] ?? null,
            'address_1'         => $validated['address_1'] ?? null,
            'city'              => $validated['city'] ?? null,
            'state'             => $validated['state'] ?? null,
            'country'           => $validated['country'] ?? 'US',
            'postal_code'       => $validated['postal_code'] ?? null,
            'status'            => 'active',
            'email_verified_at' => now(),
            'balance' => 0.0000,
        ]);

        $this->activityLogger->log(
            'client_created',
            "Admin created client account #{$client->id} ({$client->email})",
            $client,
            $client->toArray()
        );

        return redirect()->route('admin.clients.show', $client->id)->with('success', 'Client created successfully.');
    }

    /**
     * Display client profile, domain list, invoices, and ledger.
     */
    public function show($id)
    {
        $client = User::where('role', 'client')
            ->withCount('domains', 'invoices', 'tickets')
            ->findOrFail($id);

        $domains = $client->domains()->latest()->take(10)->get();
        $invoices = $client->invoices()->latest()->take(10)->get();
        $transactions = $client->transactions()->latest()->take(10)->get();
        $tickets = $client->tickets()->latest()->take(10)->get();

        return view('admin.clients.show', compact('client', 'domains', 'invoices', 'transactions', 'tickets'));
    }

    /**
     * Show form to edit client details.
     */
    public function edit($id)
    {
        $client = User::where('role', 'client')->findOrFail($id);
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update client profile.
     */
    public function update(Request $request, $id)
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($client->id)],
            'phone'        => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address_1'    => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:100'],
            'state'        => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:2'],
            'postal_code'  => ['nullable', 'string', 'max:20'],
            'status'       => ['required', 'in:active,suspended,pending'],
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['min:8']]);
            $validated['password'] = Hash::make($request->input('password'));
        }

        $client->update($validated);

        $this->activityLogger->log(
            'client_updated',
            "Admin updated client #{$client->id} ({$client->email})",
            $client,
            $validated
        );

        return redirect()->route('admin.clients.show', $client->id)->with('success', 'Client details updated.');
    }

    /**
     * Delete client account if no active registered domains exist.
     */
    public function destroy($id)
    {
        $client = User::where('role', 'client')->findOrFail($id);

        if ($client->domains()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete client with active domain names.');
        }

        $this->activityLogger->log('client_deleted', "Admin deleted client #{$client->id} ({$client->email})");
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Client account removed.');
    }

    /**
     * Toggle status between active and suspended.
     */
    public function toggleStatus($id)
    {
        $client = User::where('role', 'client')->findOrFail($id);
        $newStatus = $client->status === 'active' ? 'suspended' : 'active';
        $client->update(['status' => $newStatus]);

        $this->activityLogger->log('client_status_toggled', "Admin toggled client #{$client->id} status to {$newStatus}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'status' => $newStatus]);
        }

        return back()->with('success', "Client status updated to {$newStatus}.");
    }

    /**
     * Manually credit or debit client wallet balance.
     */
    public function adjustBalance(Request $request, $id)
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $request->validate([
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'type'        => ['required', 'in:credit,debit'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $amount = (float) $request->input('amount');
        $type = $request->input('type');
        $currentBalance = (float) $client->wallet_balance;

        if ($type === 'debit' && $currentBalance < $amount) {
            return back()->with('error', 'Client has insufficient balance for this debit.');
        }

        $newBalance = $type === 'credit' ? $currentBalance + $amount : $currentBalance - $amount;
        if ($type === 'credit') $client->creditBalance($amount); else $client->deductBalance($amount);

        Transaction::create([
            'user_id'          => $client->id,
            'type'             => $type,
            'amount'           => $amount,
            'currency'         => 'USD',
            'exchange_rate'    => 1.000000,
            'gateway' => 'manual', 'amount_in_usd' => $amount,
            'reference_id'     => 'ADJ-' . strtoupper(uniqid()),
            'status'           => 'completed',
            'description'      => $request->input('description') . ' (Adjusted by Admin)',
        ]);

        $this->activityLogger->log(
            'balance_adjusted',
            "Admin {$type}ed \${$amount} to client #{$client->id}. New balance: \${$newBalance}",
            $client
        );

        return back()->with('success', "Balance {$type}ed successfully. New balance: \${$newBalance}");
    }

    /**
     * Log in as client (Impersonate).
     */
    public function impersonate($id)
    {
        $client = User::where('role', 'client')->findOrFail($id);
        session(['admin_impersonator_id' => Auth::id()]);
        Auth::login($client);

        return redirect()->route('client.dashboard')->with('info', "You are now impersonating {$client->name}.");
    }
}
