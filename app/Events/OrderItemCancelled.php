<?php

namespace App\Events;

use App\Models\OrderItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OrderItem $orderItem)
    {
        $this->orderItem->load(['order.table', 'menuItem.dish']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen'),
            new PrivateChannel('dashboard'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderItemCancelled';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->orderItem->id,
            'order_id' => $this->orderItem->order_id,
            'table_number' => $this->orderItem->order->table->table_number ?? 'N/A',
            'name' => $this->orderItem->menuItem->dish->name ?? __('Item'),
            'status' => 'cancelled',
        ];
    }
}
