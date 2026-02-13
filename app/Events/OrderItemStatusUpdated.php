<?php

namespace App\Events;

use App\Models\OrderItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OrderItem $orderItem)
    {
        // Load relationships needed for broadcast data
        $this->orderItem->load(['order.table', 'menuItem.dish']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderItemStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->orderItem->id,
            'order_id' => $this->orderItem->order_id,
            'table_number' => $this->orderItem->order->table->table_number ?? 'N/A',
            'name' => $this->orderItem->menuItem->dish->name,
            'quantity' => $this->orderItem->quantity,
            'unit_price' => $this->orderItem->unit_price,
            'notes' => $this->orderItem->notes,
            'status' => $this->orderItem->status,
            'updated_at_human' => $this->orderItem->updated_at->diffForHumans(),
            'update_url' => route('kitchen.update-status', $this->orderItem->id),
        ];
    }
}
