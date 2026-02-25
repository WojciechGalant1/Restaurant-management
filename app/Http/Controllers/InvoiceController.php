<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Invoice;
use App\Enums\BillStatus;
use App\Services\InvoiceService;
use App\Http\Requests\StoreInvoiceRequest;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Invoice::class);
        $invoices = Invoice::with('bill.order.table')->latest()->paginate(20);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $this->authorize('create', Invoice::class);
        $bills = Bill::with('order.table')
            ->where('status', BillStatus::Paid)
            ->whereDoesntHave('invoice')
            ->latest()
            ->get();
        return view('invoices.create', compact('bills'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['bill.payments', 'bill.order.orderItems.menuItem.dish', 'bill.order.table']);
        return view('invoices.show', compact('invoice'));
    }
}
