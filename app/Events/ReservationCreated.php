<?php

namespace App\Events;

use App\Data\DashboardFeedPayload;
use App\Models\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationCreated implements ShouldBroadcast
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
        return 'ReservationCreated';
    }

    public function broadcastWith(): array
    {
        $payload = new DashboardFeedPayload(
            type: 'reservation_created',
            message: __('Reservation for :name – :date', [
                'name' => $this->reservation->customer_name,
                'date' => \Carbon\Carbon::parse($this->reservation->reservation_date)->format('d.m.Y'),
            ]),
            time: now()->format('H:i'),
            link: route('reservations.index'),
        );
        return $payload->toArray();
    }
}
