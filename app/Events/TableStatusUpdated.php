<?php

namespace App\Events;

use App\Models\Table;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TableStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Table $table)
    {
        $this->table->loadMissing('activeAssignment.user');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tables'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TableStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->table->id,
            'table_number' => $this->table->table_number,
            'capacity' => $this->table->capacity,
            'status' => $this->table->status->value,
            'status_label' => $this->table->status->label(),
            'waiter_name' => $this->table->activeAssignment?->user?->name,
            'waiter_id' => $this->table->activeAssignment?->user_id,
            'shift_id' => $this->table->activeAssignment?->shift_id,
        ];
    }
}
