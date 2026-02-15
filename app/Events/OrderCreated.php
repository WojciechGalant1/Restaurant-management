<?php

namespace App\Events;

use App\Data\DashboardFeedPayload;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->load(['table']);
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'OrderCreated';
    }

    public function broadcastWith(): array
    {
        $tableNumber = $this->order->table->table_number ?? 'N/A';
        $payload = new DashboardFeedPayload(
            type: 'order_created',
            message: __('Order #:id created – Table :table', ['id' => $this->order->id, 'table' => $tableNumber]),
            time: now()->format('H:i'),
            link: route('orders.show', $this->order),
        );
        return $payload->toArray();
    }
}
