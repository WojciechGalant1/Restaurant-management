<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use App\Enums\OrderStatus;
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
            ->each(function ($order) use ($menuItems) {
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
                $totalPrice = $items->sum(function ($item) {
                     return $item->unit_price * $item->quantity;
                });
                $order->update(['total_price' => $totalPrice]);

                // Create invoice if paid
                if ($order->status === OrderStatus::Paid) {
                    Invoice::factory()
                        ->for($order)
                        ->create([
                            'amount' => $totalPrice,
                            'issued_at' => $order->paid_at ?? now(),
                        ]);
                }
            });
    }
}
