<?php

namespace App\Services\Dashboard;

use App\Models\Shift;
use App\Models\User;

class StaffShiftService
{
    public function staffOnShiftToday(): array
    {
        $today = today();
        $userIds = Shift::query()
            ->whereDate('date', $today)
            ->pluck('user_id');
        $roles = User::query()
            ->whereIn('id', $userIds)
            ->selectRaw('role, COUNT(*) as c')
            ->groupBy('role')
            ->pluck('c', 'role')
            ->all();
        return [
            'chef' => (int) ($roles['chef'] ?? 0),
            'waiter' => (int) ($roles['waiter'] ?? 0),
            'manager' => (int) ($roles['manager'] ?? 0),
        ];
    }

    public function nextShiftChange(): ?string
    {
        $today = today();
        $next = Shift::query()
            ->where('date', '>=', $today)
            ->orderBy('date')
            ->orderBy('shift_type')
            ->first();
        if (!$next) {
            return null;
        }
        $date = $next->date->format('Y-m-d');
        $time = $next->shift_type === 'morning' ? '08:00' : ($next->shift_type === 'evening' ? '16:00' : '08:00');
        return "{$date} {$time}";
    }
}
