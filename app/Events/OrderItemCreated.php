<?php

namespace App\Events;

use App\Models\OrderItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OrderItem $orderItem)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->orderItem->id,
            'order_id' => $this->orderItem->order_id,
            'table_number' => $this->orderItem->order->table->table_number ?? 'N/A',
            'name' => $this->orderItem->menuItem->dish->name,
            'quantity' => $this->orderItem->quantity,
            'notes' => $this->orderItem->notes,
            'status' => $this->orderItem->status,
            'created_at' => $this->orderItem->created_at->toDateTimeString(),
            'created_at_human' => $this->orderItem->created_at->diffForHumans(),
        ];
    }
}
