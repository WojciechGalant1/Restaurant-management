<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Events\OrderItemCreated;
use App\Models\Order;
use App\Models\Table;
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
                'status' => OrderStatus::Open,
                'total_price' => 0,
            ]);

            foreach ($data['items'] as $item) {
                $orderItem = $order->orderItems()->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                    'status' => OrderItemStatus::Pending,
                ]);

                // Refresh to ensure we have the latest data
                $orderItem->refresh();

                event(new OrderItemCreated($orderItem));
            }

            $order->update(['total_price' => $order->orderItems->sum(fn($i) => $i->quantity * $i->unit_price)]);

            $table = Table::find($data['table_id']);
            if ($table) {
                $table->markAsOccupied();
            }

            event(new OrderCreated($order->load('table')));

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
                            $submittedIds[] = $orderItem->id;
                        
                    } else {
                        $orderItem = $order->orderItems()->create(array_merge($payload, ['status' => OrderItemStatus::Pending]));
                        $orderItem->refresh();
                        $submittedIds[] = $orderItem->id;
                        event(new OrderItemCreated($orderItem));
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
        $result = $order->update(['status' => $status]);

        if ($result && in_array($status, [OrderStatus::Paid->value, OrderStatus::Cancelled->value])) {
            $this->releaseTableIfFree($order);
        }

        return $result;
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

        if (!$hasOtherOpenOrders) {
            $table->markAsAvailable();
        }
    }
}
