<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Bill;
use App\Models\Order;
use App\Services\BillService;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function __construct(
        private BillService $billService
    ) {}

    public function store(Request $request, Order $order)
    {
        $this->authorize('create', Bill::class);

        try {
            $bill = $this->billService->createBill($order);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', __('Bill created. You can now add payments.'));
    }

    public function addPayment(Request $request, Bill $bill)
    {
        $this->authorize('addPayment', $bill);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => ['required', 'in:cash,card,online'],
        ]);

        try {
            $this->billService->addPayment(
                $bill,
                (float) $validated['amount'],
                PaymentMethod::from($validated['method'])
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('orders.show', $bill->order)
            ->with('success', __('Payment added.'));
    }

    public function cancel(Request $request, Bill $bill)
    {
        $this->authorize('cancel', $bill);

        try {
            $this->billService->cancelBill($bill);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('orders.show', $bill->order)
            ->with('success', __('Bill cancelled. Order can be edited again.'));
    }
}
