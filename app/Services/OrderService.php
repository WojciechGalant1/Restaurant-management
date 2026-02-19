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

            event(new \App\Events\OrderCreated($order->load('table')));

            return $order;
        });
    }



    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            if (isset($data['table_id'])) {
                $order->update(['table_id' => $data['table_id']]);
            }

            if (isset($data['items']) && is_array($data['items'])) {
                $submittedIds = [];
                foreach ($data['items'] as $item) {
                    $payload = [
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => (float) $item['unit_price'],
                        'notes' => $item['notes'] ?? null,
                    ];
                    if (!empty($item['id']) && (int) $item['id'] > 0) {
                        $orderItem = $order->orderItems()->find($item['id']);
                        if ($orderItem) {
                            $orderItem->update($payload);
                            $submittedIds[] = $orderItem->id;
                        }
                    } else {
                        $orderItem = $order->orderItems()->create(array_merge($payload, ['status' => 'pending']));
                        $orderItem->refresh();
                        $submittedIds[] = $orderItem->id;
                        event(new \App\Events\OrderItemCreated($orderItem));
                    }
                }
                $order->orderItems()->whereNotIn('id', $submittedIds)->delete();
            }

            $order->update([
                'total_price' => $order->orderItems()->get()->sum(fn ($i) => $i->quantity * $i->unit_price),
            ]);

            return $order->fresh(['table', 'orderItems.menuItem']);
        });
    }

    public function updateStatus(Order $order, string $status): bool
    {
        return $order->update(['status' => $status]);
    }
}
