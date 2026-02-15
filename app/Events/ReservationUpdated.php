<?php

namespace App\Events;

use App\Data\DashboardFeedPayload;
use App\Models\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Reservation $reservation)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'ReservationUpdated';
    }

    public function broadcastWith(): array
    {
        $payload = new DashboardFeedPayload(
            type: 'reservation_updated',
            message: __('Reservation #:id updated – :name', [
                'id' => $this->reservation->id,
                'name' => $this->reservation->customer_name,
            ]),
            time: now()->format('H:i'),
            link: route('reservations.index'),
        );
        return $payload->toArray();
    }
}
