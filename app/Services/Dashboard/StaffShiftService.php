<?php

namespace App\Services\Dashboard;

use App\Models\Shift;
use App\Models\User;

class StaffShiftService
{
    /**
     * Returns the count of staff currently on an active shift, grouped by role.
     * Uses the Shift::activeNow() scope for DB-level filtering.
     */
    public function staffOnShiftToday(): array
    {
        $activeUserIds = Shift::activeNow()
            ->pluck('user_id')
            ->unique();

        if ($activeUserIds->isEmpty()) {
            return [
                'chef' => 0,
                'waiter' => 0,
                'manager' => 0,
                'bartender' => 0,
            ];
        }

        $roles = User::query()
            ->whereIn('id', $activeUserIds)
            ->selectRaw('role, COUNT(*) as c')
            ->groupBy('role')
            ->pluck('c', 'role')
            ->all();

        return [
            'chef' => (int) ($roles['chef'] ?? 0),
            'waiter' => (int) ($roles['waiter'] ?? 0),
            'manager' => (int) ($roles['manager'] ?? 0),
            'bartender' => (int) ($roles['bartender'] ?? 0),
        ];
    }

    /**
     * Returns the datetime of the next upcoming shift change.
     * Uses start_time/end_time columns from the database.
     */
    public function nextShiftChange(): ?string
    {
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        // First: check today's shifts that haven't ended yet
        $todayEndingShift = Shift::query()
            ->where('date', $today)
            ->where('end_time', '>', $currentTime)
            ->orderBy('end_time')
            ->first();

        if ($todayEndingShift) {
            return "{$today} {$todayEndingShift->end_time}";
        }

        // Then: find the next future shift
        $next = Shift::query()
            ->where('date', '>', $today)
            ->orderBy('date')
            ->orderBy('start_time')
            ->first();

        if (!$next) {
            return null;
        }

        $date = $next->date->format('Y-m-d');
        return "{$date} {$next->start_time}";
    }
}
