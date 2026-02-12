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
            'status' => $this->orderItem->status,
            'updated_at_human' => $this->orderItem->updated_at->diffForHumans(),
        ];
    }
}
