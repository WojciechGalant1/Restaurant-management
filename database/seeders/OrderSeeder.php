<?php

namespace Database\Seeders;

use App\Enums\BillStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Table;
use App\Models\User;
use App\Enums\OrderItemStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $tables = Table::all();
        $waiters = User::where('role', 'waiter')->get();
        $menuItems = MenuItem::all();

        if ($tables->isEmpty() || $waiters->isEmpty() || $menuItems->isEmpty()) {
            return;
        }

        $orderCount = rand(1500, 2000);
        
        $this->command->info("Creating {$orderCount} orders...");

        Order::factory()
            ->count($orderCount)
            ->recycle($tables)
            ->recycle($waiters)
            ->create()
            ->each(function (Order $order) use ($menuItems) {
                // Determine item status distribution based on order status
                $itemStatus = OrderItemStatus::Served;
                if ($order->status === OrderStatus::Cancelled) {
                    $itemStatus = OrderItemStatus::Cancelled;
                } elseif ($order->status === OrderStatus::Open) {
                    // Only open orders can have pending/preparing items
                    $itemStatus = function () {
                        return fake()->randomElement([
                            ...array_fill(0, 40, OrderItemStatus::Served),
                            ...array_fill(0, 25, OrderItemStatus::Ready),
                            ...array_fill(0, 20, OrderItemStatus::Preparing),
                            ...array_fill(0, 15, OrderItemStatus::Pending),
                        ]);
                    };
                }

                // Attach random order items
                $items = OrderItem::factory()
                    ->count(rand(1, 5))
                    ->for($order)
                    ->recycle($menuItems)
                    ->create([
                        'status' => is_callable($itemStatus) ? $itemStatus() : $itemStatus,
                        'created_at' => $order->ordered_at,
                        'updated_at' => $order->ordered_at,
                    ]);

                // Recalculate total price
                $totalPrice = $items->sum(fn ($item) => $item->unit_price * $item->quantity);
                $order->update(['total_price' => $totalPrice]);

                // For paid orders: create Bill → Payment → Invoice chain
                if ($order->status === OrderStatus::Paid) {
                    $paidAt = $order->paid_at ?? now();

                    $bill = Bill::create([
                        'order_id' => $order->id,
                        'status' => BillStatus::Paid,
                        'total_amount' => $totalPrice,
                        'tip_amount' => $this->shouldApplyTip() ? round($totalPrice * rand(5, 15) / 100, 2) : null,
                        'paid_at' => $paidAt,
                    ]);

                    Payment::create([
                        'bill_id' => $bill->id,
                        'amount' => $bill->total_amount + ($bill->tip_amount ?? 0),
                        'method' => fake()->randomElement(PaymentMethod::cases()),
                        'created_at' => $paidAt,
                    ]);

                    // Invoice issued_at >= paid_at
                    $invoiceIssuedAt = Carbon::parse($paidAt)->addSeconds(rand(5, 60));

                    Invoice::create([
                        'bill_id' => $bill->id,
                        'invoice_number' => 'INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                        'amount' => $totalPrice,
                        'tax_id' => fake()->optional(0.7)->numerify('##########'),
                        'customer_name' => fake()->optional(0.7)->name(),
                        'issued_at' => $invoiceIssuedAt,
                    ]);
                }
            });
    }

    private function shouldApplyTip(): bool
    {
        return rand(1, 100) <= 30; // 30% chance for a tip
    }
}
