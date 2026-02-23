<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInvoiceRequest;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
        ) {}

    public function index()
    {
        $this->authorize('viewAny', Invoice::class);
        $invoices = Invoice::with('order.table')->latest()->paginate(20);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $this->authorize('create', Invoice::class);
        $orders = Order::where('status', '!=', OrderStatus::Paid)->get();
        return view('invoices.create', compact('orders'));
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
        return view('invoices.show', compact('invoice'));
    }
}
