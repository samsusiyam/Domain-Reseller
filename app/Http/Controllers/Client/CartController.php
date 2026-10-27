<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index()
    {
        $validation = $this->cartService->validateCart();
        $items = $this->cartService->getItems();
        $totals = $this->cartService->getTotals();

        return view('cart.index', [
            'items' => $items,
            'totals' => $totals,
            'warnings' => $validation['warnings'],
            'isValid' => $validation['is_valid'],
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'action' => ['nullable', 'in:register,renew,transfer'],
            'years' => ['nullable', 'integer', 'min:1', 'max:10'],
            'epp_code' => ['nullable', 'string', 'max:255'],
            'buy_now' => ['nullable', 'boolean'],
        ]);

        $item = $this->cartService->addItem(
            domain: $request->input('domain'),
            action: $request->input('action', 'register'),
            years: (int) $request->input('years', 1),
            eppCode: $request->input('epp_code'),
            user: auth()->user()
        );

        $totals = $this->cartService->getTotals();

        // If Buy Now is requested, redirect directly to checkout
        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$item['domain']} added to cart.",
                'item' => $item,
                'count' => $totals['item_count'],
                'totals' => $totals,
            ]);
        }

        return back()->with('success', "{$item['domain']} added to cart.");
    }

    public function update(Request $request, string $domain)
    {
        $request->validate([
            'years' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $item = $this->cartService->updateYears($domain, (int) $request->input('years'));

        if (!$item) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
            }
            return back()->with('error', 'Item not found in cart.');
        }

        $totals = $this->cartService->getTotals();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'item' => $item,
                'totals' => $totals,
            ]);
        }

        return back()->with('success', "Updated {$domain} period.");
    }

    public function remove(Request $request, string $domain)
    {
        $removed = $this->cartService->removeItem($domain);
        $totals = $this->cartService->getTotals();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $removed,
                'count' => $totals['item_count'],
                'totals' => $totals,
            ]);
        }

        return back()->with('success', "Removed {$domain} from cart.");
    }

    public function applyPromo(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $res = $this->cartService->applyPromoCode($request->input('code'));

        if (!$res['success']) {
            return back()->with('error', $res['message']);
        }

        return back()->with('success', 'Promo code applied successfully.');
    }

    public function removePromo()
    {
        $this->cartService->removePromoCode();
        return back()->with('success', 'Promo code removed.');
    }
}
