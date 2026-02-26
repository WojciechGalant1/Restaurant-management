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

        Order::factory()
            ->count(50)
            ->recycle($tables)
            ->recycle($waiters)
            ->create()
            ->each(function (Order $order) use ($menuItems) {
                // Attach random order items
                $items = OrderItem::factory()
                    ->count(rand(1, 5))
                    ->for($order)
                    ->recycle($menuItems)
                    ->create([
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
                        'tip_amount' => null,
                        'paid_at' => $paidAt,
                    ]);

                    Payment::create([
                        'bill_id' => $bill->id,
                        'amount' => $totalPrice,
                        'method' => fake()->randomElement(PaymentMethod::cases()),
                    ]);

                    Invoice::create([
                        'bill_id' => $bill->id,
                        'invoice_number' => 'INV-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                        'amount' => $totalPrice,
                        'tax_id' => fake()->optional()->numerify('##########'),
                        'customer_name' => fake()->optional()->name(),
                        'issued_at' => $paidAt,
                    ]);
                }
            });
    }
}
