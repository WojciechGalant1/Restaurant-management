<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Order;
use App\Enums\ReservationStatus;
use App\Events\ReservationCreated;
use App\Events\ReservationUpdated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationService
{

    public function createReservation(array $data): Reservation
    {
        $data['status'] = ReservationStatus::Pending;

        $reservation = Reservation::create($data);
        event(new ReservationCreated($reservation));

        return $reservation;
    }

    public function updateStatus(Reservation $reservation, ReservationStatus|string $newStatus): void
    {
        if (is_string($newStatus)) {
            $newStatus = ReservationStatus::from($newStatus);
        }

        $currentStatus = $reservation->status;

        if (!$this->isTransitionAllowed($currentStatus, $newStatus)) {
            throw new \InvalidArgumentException("Cannot change status from {$currentStatus->value} to {$newStatus->value}.");
        }

        $reservation->update(['status' => $newStatus]);
        event(new ReservationUpdated($reservation));
    }

    public function autoCompleteForTable(int $tableId, string $date): void
    {
        $reservations = Reservation::where('table_id', $tableId)
            ->whereIn('status', [ReservationStatus::Confirmed, ReservationStatus::Seated])
            ->whereDate('reservation_date', $date)
            ->get();

        foreach ($reservations as $reservation) {
            $this->updateStatus($reservation, ReservationStatus::Completed);
        }
    }

    public function isTransitionAllowed(ReservationStatus $current, ReservationStatus $new): bool
    {
        $allowedTransitions = [
            ReservationStatus::Pending->value => [ReservationStatus::Confirmed->value, ReservationStatus::Cancelled->value],
            ReservationStatus::Confirmed->value => [ReservationStatus::Seated->value, ReservationStatus::NoShow->value, ReservationStatus::Cancelled->value],
            ReservationStatus::Seated->value => [ReservationStatus::Completed->value, ReservationStatus::Cancelled->value],
            ReservationStatus::NoShow->value => [], // terminal state
            ReservationStatus::Completed->value => [], // terminal state
            ReservationStatus::Cancelled->value => [], // terminal state
        ];

        return in_array($new->value, $allowedTransitions[$current->value] ?? []);
    }
}
