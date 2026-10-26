<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\Audit\ActivityLogger;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\Request;

class CurrencySettingController extends Controller
{
    public function __construct(
        protected CurrencyConverter $converter,
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * List all supported currencies.
     */
    public function index()
    {
        $currencies = Currency::orderByDesc('is_primary')->orderBy('code')->get();
        return view('admin.currencies.index', compact('currencies'));
    }

    /**
     * Add new currency.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'            => ['required', 'string', 'size:3', 'unique:currencies,code'],
            'name'            => ['required', 'string', 'max:50'],
            'symbol'          => ['required', 'string', 'max:10'],
            'exchange_rate'   => ['required', 'numeric', 'min:0.000001'],
            'decimal_places'  => ['required', 'integer', 'min:0', 'max:4'],
            'symbol_position' => ['required', 'in:before,after'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_primary'] = false;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['last_updated_at'] = now();

        $currency = Currency::create($validated);

        $this->activityLogger->log('currency_created', "Admin added currency {$currency->code}", $currency);

        return back()->with('success', "Currency {$currency->code} added successfully.");
    }

    /**
     * Update currency exchange rate and settings.
     */
    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:50'],
            'symbol'          => ['required', 'string', 'max:10'],
            'exchange_rate'   => ['required', 'numeric', 'min:0.000001'],
            'decimal_places'  => ['required', 'integer', 'min:0', 'max:4'],
            'symbol_position' => ['required', 'in:before,after'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['last_updated_at'] = now();

        $currency->update($validated);

        $this->activityLogger->log('currency_updated', "Admin updated currency {$currency->code}", $currency);

        return back()->with('success', "Currency {$currency->code} updated.");
    }

    /**
     * Trigger live exchange rate update from external API.
     */
    public function updateRates()
    {
        try {
            $this->converter->syncRates();
            return back()->with('success', 'Exchange rates synchronized successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to synchronize exchange rates: ' . $e->getMessage());
        }
    }
}
