<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Reservation;
use App\Enums\ReservationStatus;
use App\Enums\OrderStatus;
use App\Events\InvoiceIssued;
use App\Events\ReservationUpdated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $order = Order::findOrFail($data['order_id']);

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'amount' => $order->total_price,
                'customer_name' => $data['customer_name'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'payment_method' => $data['payment_method'],
                'issued_at' => now(),
            ]);

            $order->update([
                'status' => OrderStatus::Paid,
                'paid_at' => now()
            ]);

            $this->autoCompleteReservations($order);
            $this->clearRevenueCaches();

            event(new InvoiceIssued($invoice));

            return $invoice;
        });
    }

    private function autoCompleteReservations(Order $order): void
    {
        if (!$order->table_id) {
            return;
        }

        $orderDate = Carbon::parse($order->ordered_at ?? $order->created_at)->toDateString();
        $this->reservationService->autoCompleteForTable($order->table_id, $orderDate);
    }

    public function clearRevenueCaches(): void
    {
        Cache::forget('dashboard:revenue_by_day:7');
        Cache::forget('dashboard:revenue_by_day:30');
        Cache::forget('dashboard:revenue_this_month:' . now()->format('Y-m'));
        Cache::forget('dashboard:payment_breakdown');
        Cache::forget('dashboard:kpis');
    }
}
