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
        $data['status'] = ReservationStatus::Confirmed;

        $reservation = Reservation::create($data);

        // Only mark as Reserved if the reservation is actually for today
        if ($reservation->table && $reservation->reservation_date->isToday()) {
            $reservation->table->markAsReserved();
        }

        event(new ReservationCreated($reservation));

        return $reservation;
    }

    /**
     * Seat walk-in guests (no prior reservation): create a reservation in WalkInSeated
     * status so the table status is driven by ReservationService.
     */
    public function seatWalkIn(Table $table, int $partySize = 1): Reservation
    {
        return DB::transaction(function () use ($table, $partySize) {
            $now = now();

            $reservation = Reservation::create([
                'table_id' => $table->id,
                'customer_name' => 'Walk-in',
                'phone_number' => 'walk-in',
                'reservation_date' => $now->toDateString(),
                'reservation_time' => $now->format('H:i:s'),
                'party_size' => max(1, $partySize),
                'duration_minutes' => 120,
                'status' => ReservationStatus::WalkInSeated,
            ]);

            $this->syncTableStatusAfterReservation($reservation, ReservationStatus::WalkInSeated);
            event(new ReservationCreated($reservation));

            return $reservation;
        });
    }

    public function updateReservation(Reservation $reservation, array $data): void
    {
        $oldTableId = $reservation->table_id;
        $oldDate = $reservation->reservation_date;

        $reservation->update($data);

        // Sync old table if it changed or if date changed
        if ($oldTableId !== $reservation->table_id || !$oldDate->isSameDay($reservation->reservation_date)) {
            $oldTable = Table::find($oldTableId);
            if ($oldTable) {
                $this->releaseTableIfFree($oldTable);
            }
        }

        // Sync current table status
        $this->syncTableStatusAfterReservation($reservation, $reservation->status);
        
        event(new ReservationUpdated($reservation));
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
            // Only mark as Reserved if the reservation is for today
            ReservationStatus::Confirmed => Carbon::parse($reservation->reservation_date)->isToday()
                ? $table->markAsReserved()
                : $this->releaseTableIfFree($table),
            ReservationStatus::WalkInSeated => $table->markAsOccupied(),
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

        // Only count today's reservations — future confirmed reservations should not block table release
        $hasActiveReservations = Reservation::where('table_id', $table->id)
            ->whereIn('status', [ReservationStatus::Confirmed, ReservationStatus::WalkInSeated, ReservationStatus::Seated])
            ->whereDate('reservation_date', today())
            ->exists();

        if (!$hasOpenOrders && !$hasActiveReservations) {
            $table->markAsAvailable();
        }
    }

    /**
     * Mark reservations as Completed when the table is cleared (e.g. after invoice).
     * Completed must not be set manually from the UI.
     */
    public function autoCompleteForTable(int $tableId, string $date): void
    {
        $reservations = Reservation::where('table_id', $tableId)
            ->whereIn('status', [ReservationStatus::Confirmed, ReservationStatus::WalkInSeated, ReservationStatus::Seated])
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
            ReservationStatus::WalkInSeated->value => [ReservationStatus::Completed->value, ReservationStatus::Cancelled->value],
            ReservationStatus::NoShow->value => [], // terminal state
            ReservationStatus::Completed->value => [], // terminal state
            ReservationStatus::Cancelled->value => [], // terminal state
        ];

        return in_array($new->value, $allowedTransitions[$current->value] ?? []);
    }
}
