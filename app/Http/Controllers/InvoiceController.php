<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Invoice::class);
        $invoices = Invoice::with('order.table')->latest()->paginate(20);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $this->authorize('create', Invoice::class);
        $orders = Order::where('status', '!=', 'paid')->get();
        return view('invoices.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'customer_name' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'payment_method' => 'required|in:cash,card,online',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
            'amount' => $order->total_price,
            'customer_name' => $validated['customer_name'],
            'tax_id' => $validated['tax_id'],
            'payment_method' => $validated['payment_method'],
            'issued_at' => now(),
        ]);

        $order->update(['status' => 'paid', 'paid_at' => now()]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        return view('invoices.show', compact('invoice'));
    }
}
