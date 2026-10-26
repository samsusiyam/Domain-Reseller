<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrarModule;
use App\Services\Audit\ActivityLogger;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Http\Request;

class RegistrarModuleController extends Controller
{
    public function __construct(
        protected RegistrarManager $registrarManager,
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * List all supported registrar modules.
     */
    public function index()
    {
        $registrars = RegistrarModule::withCount('tlds', 'domains')->get();
        return view('admin.registrars.index', compact('registrars'));
    }

    /**
     * Edit registrar configuration.
     */
    public function edit($id)
    {
        $registrar = RegistrarModule::findOrFail($id);
        return view('admin.registrars.edit', compact('registrar'));
    }

    /**
     * Update registrar configuration and credentials.
     */
    public function update(Request $request, $id)
    {
        $registrar = RegistrarModule::findOrFail($id);

        $request->validate([
            'environment' => ['required', 'in:production,sandbox'],
            'config'      => ['required', 'array'],
        ]);

        $registrar->update([
            'environment' => $request->input('environment'),
            'config'      => $request->input('config'),
            'is_active'   => $request->boolean('is_active'),
        ]);

        $this->activityLogger->log('registrar_updated', "Admin updated registrar module {$registrar->name}", $registrar);

        return redirect()->route('admin.registrars.index')->with('success', "{$registrar->name} configuration updated.");
    }

    /**
     * Test API connection with registrar.
     */
    public function testConnection($id)
    {
        $registrarModel = RegistrarModule::findOrFail($id);

        try {
            $driver = $this->registrarManager->driver($registrarModel->slug);
            $balance = $driver->getAccountBalance();

            $registrarModel->update([
                'balance'              => $balance->balance,
                'currency'             => $balance->currency,
                'last_balance_sync_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully connected to {$registrarModel->name}! Current Balance: {$balance->formatted()}",
                'balance' => $balance->balance,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Connection failed: " . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Synchronize account balance.
     */
    public function syncBalance($id)
    {
        $registrarModel = RegistrarModule::findOrFail($id);

        try {
            $driver = $this->registrarManager->driver($registrarModel->slug);
            $balance = $driver->getAccountBalance();

            $registrarModel->update([
                'balance'              => $balance->balance,
                'currency'             => $balance->currency,
                'last_balance_sync_at' => now(),
            ]);

            return back()->with('success', "Balance for {$registrarModel->name} synced: {$balance->formatted()}");
        } catch (\Throwable $e) {
            return back()->with('error', "Balance sync failed: " . $e->getMessage());
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $registrarModel = RegistrarModule::findOrFail($id);
        $registrarModel->update(['is_active' => !$registrarModel->is_active]);

        $this->activityLogger->log('registrar_status_toggled', "Admin toggled {$registrarModel->name} status to " . ($registrarModel->is_active ? 'Active' : 'Inactive'));

        return back()->with('success', "{$registrarModel->name} status updated.");
    }

    /**
     * Set default registrar module.
     */
    public function setDefault(Request $request)
    {
        $request->validate(['registrar_id' => 'required|exists:registrar_modules,id']);

        RegistrarModule::query()->update(['is_default' => false]);
        $registrar = RegistrarModule::findOrFail($request->input('registrar_id'));
        $registrar->update(['is_default' => true]);

        return back()->with('success', "{$registrar->name} set as default registrar.");
    }
}
