<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // Auto-complete reservations for this table (visit finished with payment)
        $order->load('table');
        if ($order->table_id) {
            $orderDate = Carbon::parse($order->ordered_at ?? $order->created_at)->toDateString();
            $reservations = Reservation::where('table_id', $order->table_id)
                ->whereIn('status', ['confirmed', 'seated'])
                ->whereDate('reservation_date', $orderDate)
                ->get();
            foreach ($reservations as $reservation) {
                $reservation->update(['status' => 'completed']);
                event(new \App\Events\ReservationUpdated($reservation));
            }
        }

        // Clear dashboard revenue caches when new invoice is created
        $this->clearRevenueCaches();

        event(new \App\Events\InvoiceIssued($invoice));

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        return view('invoices.show', compact('invoice'));
    }

    private function clearRevenueCaches(): void
    {
        // Clear revenue chart caches (7 days, 30 days)
        Cache::forget('dashboard:revenue_by_day:7');
        Cache::forget('dashboard:revenue_by_day:30');
        
        // Clear revenue this month cache
        Cache::forget('dashboard:revenue_this_month:' . now()->format('Y-m'));
        
        // Clear payment breakdown cache
        Cache::forget('dashboard:payment_breakdown');
        
        // Clear KPI cache (includes revenue_today, orders_today, etc.)
        Cache::forget('dashboard:kpis');
        
        // Note: revenueBetween() cache keys include dates, so they expire naturally (60s TTL)
        // If you need immediate invalidation for custom ranges, consider cache tags (Redis) or
        // a pattern-based flush (e.g., Cache::flush() for all dashboard:* keys - use with caution)
    }
}
