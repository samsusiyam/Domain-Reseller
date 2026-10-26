<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiBillingController extends BaseApiController
{
    /**
     * Reseller invoices list.
     */
    public function invoices(Request $request): JsonResponse
    {
        $invoices = Invoice::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return $this->sendResponse($invoices, 'Invoices list retrieved.');
    }

    /**
     * Single invoice details.
     */
    public function invoice(Request $request, $id): JsonResponse
    {
        $invoice = Invoice::where('user_id', $request->user()->id)
            ->with('items', 'transactions')
            ->findOrFail($id);

        return $this->sendResponse($invoice, 'Invoice retrieved.');
    }

    /**
     * Reseller wallet transaction history.
     */
    public function transactions(Request $request): JsonResponse
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return $this->sendResponse($transactions, 'Transaction ledger retrieved.');
    }

    /**
     * Reseller domain orders queue.
     */
    public function orders(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->paginate(20);

        return $this->sendResponse($orders, 'Orders list retrieved.');
    }

    /**
     * Single order details and provisioning response.
     */
    public function order(Request $request, $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with('items.domain')
            ->findOrFail($id);

        return $this->sendResponse($order, 'Order details retrieved.');
    }
}
