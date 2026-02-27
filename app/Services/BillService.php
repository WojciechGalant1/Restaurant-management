<?php

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReservationStatus;
use App\Models\Bill;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BillService
{
    public function __construct(
        private ReservationService $reservationService
    ) {}

    public function createBill(Order $order): Bill
    {
        return DB::transaction(function () use ($order) {
            $order->load('orderItems');

            $allowedItemStatuses = [OrderItemStatus::Served, OrderItemStatus::Cancelled, OrderItemStatus::Voided];
            $unserved = $order->orderItems->filter(
                fn ($item) => ! in_array($item->status, $allowedItemStatuses, true)
            );
            if ($unserved->isNotEmpty()) {
                throw new \InvalidArgumentException(__('Cannot create bill with unserved or unprepared items.'));
            }

            $openBill = $order->openBill();
            if ($openBill) {
                throw new \InvalidArgumentException(__('This order already has an open bill.'));
            }

            return Bill::create([
                'order_id' => $order->id,
                'status' => BillStatus::Open,
                'total_amount' => $order->total_price,
            ]);
        });
    }

    public function addPayment(Bill $bill, float $amount, PaymentMethod $method): Payment
    {
        return DB::transaction(function () use ($bill, $amount, $method) {
            if ($bill->status !== BillStatus::Open) {
                throw new \InvalidArgumentException(__('Cannot add payment to a closed bill.'));
            }

            $payment = Payment::create([
                'bill_id' => $bill->id,
                'amount' => $amount,
                'method' => $method,
            ]);

            $totalPaid = $bill->fresh()->totalPaid();
            $totalAmount = (float) $bill->total_amount;

            if ($totalPaid >= $totalAmount) {
                $tipAmount = max(0, $totalPaid - $totalAmount);
                $bill->update([
                    'status' => BillStatus::Paid,
                    'paid_at' => now(),
                    'tip_amount' => $tipAmount > 0 ? $tipAmount : null,
                ]);

                $order = $bill->order;
                $order->update([
                    'status' => OrderStatus::Paid,
                    'paid_at' => now(),
                ]);

                $this->autoCompleteReservations($order);
                $this->releaseTableIfFree($order);
                $this->clearRevenueCaches();
            }

            return $payment;
        });
    }

    public function cancelBill(Bill $bill): void
    {
        if ($bill->status !== BillStatus::Open) {
            throw new \InvalidArgumentException(__('Only open bills can be cancelled.'));
        }

        $bill->update(['status' => BillStatus::Cancelled]);
    }

    private function autoCompleteReservations(Order $order): void
    {
        if (!$order->table_id) {
            return;
        }

        $orderDate = Carbon::parse($order->ordered_at ?? $order->created_at)->toDateString();
        $this->reservationService->autoCompleteForTable($order->table_id, $orderDate);
    }

    private function releaseTableIfFree(Order $order): void
    {
        $table = $order->table;
        if (!$table) {
            return;
        }

        $hasOtherOpenOrders = Order::where('table_id', $table->id)
            ->where('id', '!=', $order->id)
            ->where('status', OrderStatus::Open)
            ->exists();

        $hasActiveReservations = Reservation::where('table_id', $table->id)
            ->whereIn('status', [
                ReservationStatus::Confirmed,
                ReservationStatus::Seated,
                ReservationStatus::WalkInSeated,
            ])
            ->exists();

        if (!$hasOtherOpenOrders && !$hasActiveReservations) {
            $table->markAsCleaning();
        }
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
