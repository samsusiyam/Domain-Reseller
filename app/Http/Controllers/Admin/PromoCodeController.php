<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\TldPricing;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Lists all promo codes.
     */
    public function index()
    {
        $promoCodes = PromoCode::withCount('orders')->latest()->paginate(15);
        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    /**
     * Show create promo code form.
     */
    public function create()
    {
        $tlds = TldPricing::where('is_active', true)->pluck('tld');
        return view('admin.promo-codes.create', compact('tlds'));
    }

    /**
     * Store new promo code.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'               => ['required', 'string', 'max:50', 'unique:promo_codes,code'],
            'description'        => ['nullable', 'string', 'max:255'],
            'discount_type'      => ['required', 'in:percentage,fixed'],
            'discount_value'     => ['required', 'numeric', 'min:0.01'],
            'max_uses'           => ['nullable', 'integer', 'min:1'],
            'user_usage_limit'   => ['nullable', 'integer', 'min:1'],
            'valid_from'         => ['nullable', 'date'],
            'valid_to'           => ['nullable', 'date', 'after_or_equal:valid_from'],
            'applicable_tlds'    => ['nullable', 'array'],
            'applicable_actions' => ['nullable', 'array'],
            'min_order_amount'   => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['current_uses'] = 0;

        $promo = PromoCode::create($validated);

        $this->activityLogger->log('promo_code_created', "Admin created promo code {$promo->code}", $promo);

        return redirect()->route('admin.promo-codes.index')->with('success', "Promo code {$promo->code} created successfully.");
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $promoCode = PromoCode::findOrFail($id);
        $tlds = TldPricing::where('is_active', true)->pluck('tld');
        return view('admin.promo-codes.edit', compact('promoCode', 'tlds'));
    }

    /**
     * Update promo code.
     */
    public function update(Request $request, $id)
    {
        $promoCode = PromoCode::findOrFail($id);

        $validated = $request->validate([
            'code'               => ['required', 'string', 'max:50', Rule::unique('promo_codes', 'code')->ignore($promoCode->id)],
            'description'        => ['nullable', 'string', 'max:255'],
            'discount_type'      => ['required', 'in:percentage,fixed'],
            'discount_value'     => ['required', 'numeric', 'min:0.01'],
            'max_uses'           => ['nullable', 'integer', 'min:1'],
            'user_usage_limit'   => ['nullable', 'integer', 'min:1'],
            'valid_from'         => ['nullable', 'date'],
            'valid_to'           => ['nullable', 'date'],
            'applicable_tlds'    => ['nullable', 'array'],
            'applicable_actions' => ['nullable', 'array'],
            'min_order_amount'   => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        $promoCode->update($validated);

        $this->activityLogger->log('promo_code_updated', "Admin updated promo code {$promoCode->code}", $promoCode);

        return redirect()->route('admin.promo-codes.index')->with('success', "Promo code {$promoCode->code} updated.");
    }

    /**
     * Delete promo code.
     */
    public function destroy($id)
    {
        $promoCode = PromoCode::findOrFail($id);
        $code = $promoCode->code;
        $promoCode->delete();

        $this->activityLogger->log('promo_code_deleted', "Admin deleted promo code {$code}");

        return redirect()->route('admin.promo-codes.index')->with('success', "Promo code {$code} deleted.");
    }
}
