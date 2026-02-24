<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TableAvailabilityService
{
    private int $conflictBufferMinutes = 15;

    /**
     * Get available tables for a given slot.
     *
     * @return array{available: array<int, array>, rooms: array}
     */
    public function forSlot(Carbon $dateTime, int $partySize, int $durationMinutes): array
    {
        $slotEnd = $dateTime->copy()->addMinutes($durationMinutes);
        $bufferStart = $dateTime->copy()->subMinutes($this->conflictBufferMinutes);
        $bufferEnd = $slotEnd->copy()->addMinutes($this->conflictBufferMinutes);

        $tables = Table::with(['room'])
            ->whereNotNull('room_id')
            ->where('capacity', '>=', $partySize)
            ->orderBy('capacity')
            ->orderBy('table_number')
            ->get();

        $activeReservationStatuses = [
            ReservationStatus::Confirmed,
            ReservationStatus::Seated,
            ReservationStatus::WalkInSeated,
        ];

        $available = [];
        foreach ($tables as $table) {
            if ($this->hasOpenOrder($table)) {
                continue;
            }

            $conflicts = $this->getOverlappingReservations(
                $table,
                $dateTime,
                $slotEnd,
                $activeReservationStatuses
            );

            if ($conflicts->isNotEmpty()) {
                continue;
            }

            $hasConflictRisk = $this->hasConflictRisk(
                $table,
                $bufferStart,
                $bufferEnd,
                $dateTime,
                $slotEnd,
                $activeReservationStatuses
            );

            $available[] = [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'capacity' => $table->capacity,
                'room_id' => $table->room_id,
                'room_name' => $table->room?->name ?? null,
                'room_color' => $table->room?->color ?? null,
                'has_conflict_risk' => $hasConflictRisk,
            ];
        }

        $rooms = $this->groupByRoom($available);

        return [
            'available' => $available,
            'rooms' => $rooms,
        ];
    }

    /**
     * Verify that a table is still available for the slot (for race-condition check before save).
     */
    public function isTableAvailableForSlot(int $tableId, Carbon $dateTime, int $durationMinutes, ?int $excludeReservationId = null): bool
    {
        $table = Table::find($tableId);
        if (!$table) {
            return false;
        }

        $slotEnd = $dateTime->copy()->addMinutes($durationMinutes);
        $activeStatuses = [
            ReservationStatus::Confirmed,
            ReservationStatus::Seated,
            ReservationStatus::WalkInSeated,
        ];

        if ($this->hasOpenOrder($table)) {
            return false;
        }

        $overlapping = Reservation::where('table_id', $tableId)
            ->whereIn('status', array_map(fn ($s) => $s->value, $activeStatuses))
            ->when($excludeReservationId, fn ($q) => $q->where('id', '!=', $excludeReservationId))
            ->get();

        foreach ($overlapping as $reservation) {
            $resStart = $this->getReservationStart($reservation);
            $resEnd = $resStart->copy()->addMinutes($reservation->duration_minutes ?? 120);
            if ($dateTime->lt($resEnd) && $slotEnd->gt($resStart)) {
                return false;
            }
        }

        return true;
    }

    private function hasOpenOrder(Table $table): bool
    {
        return $table->orders()->where('status', OrderStatus::Open)->exists();
    }

    private function getOverlappingReservations(
        Table $table,
        Carbon $slotStart,
        Carbon $slotEnd,
        array $statuses
    ): Collection {
        $statusValues = array_map(fn ($s) => $s->value, $statuses);

        return Reservation::where('table_id', $table->id)
            ->whereIn('status', $statusValues)
            ->get()
            ->filter(function (Reservation $reservation) use ($slotStart, $slotEnd) {
                $resStart = $this->getReservationStart($reservation);
                $resEnd = $resStart->copy()->addMinutes($reservation->duration_minutes ?? 120);
                return $slotStart->lt($resEnd) && $slotEnd->gt($resStart);
            });
    }

    private function hasConflictRisk(
        Table $table,
        Carbon $bufferStart,
        Carbon $bufferEnd,
        Carbon $slotStart,
        Carbon $slotEnd,
        array $statuses
    ): bool {
        $statusValues = array_map(fn ($s) => $s->value, $statuses);

        $nearby = Reservation::where('table_id', $table->id)
            ->whereIn('status', $statusValues)
            ->get();

        foreach ($nearby as $reservation) {
            $resStart = $this->getReservationStart($reservation);
            $resEnd = $resStart->copy()->addMinutes($reservation->duration_minutes ?? 120);
            if ($bufferStart->lt($resEnd) && $bufferEnd->gt($resStart)) {
                return true;
            }
        }

        return false;
    }

    private function getReservationStart(Reservation $reservation): Carbon
    {
        $dateStr = $reservation->reservation_date->format('Y-m-d');
        $timeStr = $reservation->reservation_time instanceof Carbon
            ? $reservation->reservation_time->format('H:i:s')
            : \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i:s');
        return Carbon::parse($dateStr . ' ' . $timeStr);
    }

    private function groupByRoom(array $available): array
    {
        $grouped = [];
        foreach ($available as $table) {
            $roomKey = $table['room_id'] ?? 'unassigned';
            if (!isset($grouped[$roomKey])) {
                $grouped[$roomKey] = [
                    'room_id' => $table['room_id'],
                    'room_name' => $table['room_name'] ?? __('Unassigned'),
                    'room_color' => $table['room_color'] ?? '#6b7280',
                    'tables' => [],
                ];
            }
            $grouped[$roomKey]['tables'][] = $table;
        }
        return array_values($grouped);
    }
}
