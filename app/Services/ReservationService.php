<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Events\ReservationCreated;
use App\Events\ReservationUpdated;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationService
{

    public function createReservation(array $data): Reservation
    {
        $data['status'] = ReservationStatus::Pending;

        $reservation = Reservation::create($data);

        if ($reservation->table) {
            $reservation->table->markAsReserved();
        }

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
        $this->syncTableStatusAfterReservation($reservation, $newStatus);
        event(new ReservationUpdated($reservation));
    }

    private function syncTableStatusAfterReservation(Reservation $reservation, ReservationStatus $newStatus): void
    {
        $table = $reservation->table;
        if (!$table) {
            return;
        }

        match ($newStatus) {
            ReservationStatus::Confirmed => $table->markAsReserved(),
            ReservationStatus::Seated => $table->markAsOccupied(),
            ReservationStatus::Completed,
            ReservationStatus::Cancelled,
            ReservationStatus::NoShow => $this->releaseTableIfFree($table),
            default => null,
        };
    }

    private function releaseTableIfFree(Table $table): void
    {
        $hasOpenOrders = Order::where('table_id', $table->id)
            ->where('status', OrderStatus::Open)
            ->exists();

        $hasActiveReservations = Reservation::where('table_id', $table->id)
            ->whereIn('status', [ReservationStatus::Confirmed, ReservationStatus::Seated])
            ->exists();

        if (!$hasOpenOrders && !$hasActiveReservations) {
            $table->markAsAvailable();
        }
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
