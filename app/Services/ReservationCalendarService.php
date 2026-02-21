<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationCalendarService
{
    /** Default event duration in minutes when reservation has no end time. */
    public const DEFAULT_DURATION_MINUTES = 90;

    /**
     * Reservations that overlap the given range (by reservation date + time slot).
     */
    public function getReservationsInRange(Carbon $viewStart, Carbon $viewEnd): Collection
    {
        return Reservation::with('table')
            ->whereDate('reservation_date', '>=', $viewStart->toDateString())
            ->whereDate('reservation_date', '<=', $viewEnd->toDateString())
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();
    }

    /**
     * Map reservations to FullCalendar event format.
     */
    public function reservationsToCalendarEvents(Collection $reservations): Collection
    {
        return $reservations->map(function (Reservation $reservation) {
            $rawTime = $reservation->reservation_time;
            $timeStr = $rawTime instanceof \DateTimeInterface
                ? $rawTime->format('H:i:s')
                : (is_string($rawTime) ? substr($rawTime, 0, 8) : '12:00:00');
            $start = Carbon::parse($reservation->reservation_date->format('Y-m-d') . ' ' . $timeStr);
            $end = $start->copy()->addMinutes(self::DEFAULT_DURATION_MINUTES);

            $status = $reservation->status;
            $color = match ($status->value) {
                'confirmed' => ['bg' => '#4f46e5', 'border' => '#4338ca', 'text' => '#fff'],
                'seated' => ['bg' => '#059669', 'border' => '#047857', 'text' => '#fff'],
                'completed' => ['bg' => '#6b7280', 'border' => '#4b5563', 'text' => '#fff'],
                'cancelled', 'no_show' => ['bg' => '#dc2626', 'border' => '#b91c1c', 'text' => '#fff'],
                default => ['bg' => '#f59e0b', 'border' => '#d97706', 'text' => '#fff'],
            };

            $tableNumber = $reservation->table->table_number ?? 'N/A';
            $title = $reservation->customer_name . ' · #' . $tableNumber;

            return [
                'id' => $reservation->id,
                'title' => $title,
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['border'],
                'textColor' => $color['text'],
                'extendedProps' => [
                    'customerName' => $reservation->customer_name,
                    'phoneNumber' => $reservation->phone_number,
                    'tableNumber' => $tableNumber,
                    'partySize' => $reservation->party_size,
                    'status' => $status->label(),
                    'notes' => $reservation->notes,
                    'editUrl' => route('reservations.edit', $reservation),
                ],
            ];
        });
    }
}
