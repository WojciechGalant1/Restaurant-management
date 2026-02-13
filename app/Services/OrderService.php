<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data, User $waiter): Order
    {
        return DB::transaction(function () use ($data, $waiter) {
            $order = Order::create([
                'table_id' => $data['table_id'],
                'user_id' => $waiter->id,
                'status' => 'pending',
                'total_price' => 0,
            ]);

            foreach ($data['items'] as $item) {
                $orderItem = $order->orderItems()->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                    'status' => 'pending',
                ]);

                // Refresh to ensure we have the latest data
                $orderItem->refresh();

                event(new \App\Events\OrderItemCreated($orderItem));
            }

            $order->update(['total_price' => $order->orderItems->sum(fn($i) => $i->quantity * $i->unit_price)]);

            return $order;
        });
    }

    public function updateStatus(Order $order, string $status): bool
    {
        return $order->update(['status' => $status]);
    }
}
