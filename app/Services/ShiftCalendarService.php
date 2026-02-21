<?php

namespace App\Services;

use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftCalendarService
{
    /**
     * Shifts in view range for FullCalendar (datetime overlap).
     * Optionally filtered by role (for manager) or by user_id (for staff viewing own schedule).
     */
    public function getShiftsInRange(Carbon $viewStart, Carbon $viewEnd, ?string $role = null, ?int $userId = null): Collection
    {
        $query = Shift::with('user')
            ->whereRaw('TIMESTAMP(date, start_time) < ?', [$viewEnd])
            ->whereRaw(
                'IF(end_time > start_time, TIMESTAMP(date, end_time), TIMESTAMP(DATE_ADD(date, INTERVAL 1 DAY), end_time)) > ?',
                [$viewStart]
            );

        if ($userId !== null) {
            $query->where('user_id', $userId);
        } elseif ($role !== null && $role !== '') {
            $query->whereHas('user', fn ($q) => $q->where('role', $role));
        }

        return $query->get();
    }

    /**
     * Map Shift models to FullCalendar event array.
     */
    public function shiftsToCalendarEvents(Collection $shifts): Collection
    {
        return $shifts->map(function (Shift $shift) {
            $date = $shift->date->format('Y-m-d');
            $c = $shift->shift_type->color();
            $userName = trim(($shift->user->first_name ?? '') . ' ' . ($shift->user->last_name ?? '')) ?: 'Unknown';
            $role = ucfirst($shift->user->role->value ?? '');
            $start = Carbon::parse($date . ' ' . $shift->start_time);
            $end = Carbon::parse($date . ' ' . $shift->end_time);

            if ($end <= $start) {
                $end = $end->copy()->addDay();
            }

            return [
                'id'              => $shift->id,
                'title'           => "{$userName} ({$role})",
                'start'           => $start->toIso8601String(),
                'end'             => $end->toIso8601String(),
                'backgroundColor' => $c['bg'],
                'borderColor'     => $c['border'],
                'textColor'       => $c['text'],
                'extendedProps'   => [
                    'shiftType' => $shift->shift_type->label(),
                    'userName'  => $userName,
                    'role'      => $role,
                    'notes'     => $shift->notes,
                    'editUrl'   => route('shifts.edit', $shift),
                ],
            ];
        });
    }
}
