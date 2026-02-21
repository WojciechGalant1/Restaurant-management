<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftAnalyticsService
{
    public const MAX_HOURS_PER_WEEK = 40;
    public const MAX_HOURS_PER_DAY = 12;

    /**
     * Hours per user_id for the current week (Monday–Sunday).
     */
    public function getHoursPerUserForCurrentWeek(): Collection
    {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $shifts = Shift::whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        return $shifts->groupBy('user_id')->map(
            fn ($group) => round($group->sum(fn ($s) => $s->durationInHours()), 1)
        );
    }

    /**
     * Live conflict detection / availability for AJAX.
     * Returns array keyed by user_id: shifts_today, hours_today, hours_week, conflict, exceeds_day, exceeds_week.
     */
    public function getAvailabilityForUsers(string $date, array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $base = Carbon::parse($date);
        $weekStart = $base->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $shiftsOnDate = Shift::with('user')
            ->whereIn('user_id', $userIds)
            ->where('date', $date)
            ->get()
            ->groupBy('user_id');

        $shiftsThisWeek = Shift::whereIn('user_id', $userIds)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy('user_id');

        $result = [];
        foreach ($userIds as $uid) {
            $onDate = $shiftsOnDate->get($uid, collect());
            $weekShifts = $shiftsThisWeek->get($uid, collect());
            $hoursToday = round($onDate->sum(fn ($s) => $s->durationInHours()), 1);
            $hoursWeek = round($weekShifts->sum(fn ($s) => $s->durationInHours()), 1);
            $result[$uid] = [
                'shifts_today' => $onDate->map(fn ($s) => [
                    'id'         => $s->id,
                    'shift_type' => $s->shift_type->label(),
                    'start_time' => Carbon::parse($s->start_time)->format('H:i'),
                    'end_time'   => Carbon::parse($s->end_time)->format('H:i'),
                ])->values()->all(),
                'hours_today'  => $hoursToday,
                'hours_week'   => $hoursWeek,
                'conflict'     => $onDate->isNotEmpty(),
                'exceeds_day'  => $hoursToday >= self::MAX_HOURS_PER_DAY,
                'exceeds_week' => $hoursWeek >= self::MAX_HOURS_PER_WEEK,
            ];
        }

        return $result;
    }

    /**
     * Daily coverage by role for given dates (for AJAX).
     * Returns array keyed by date: chef, waiter, bartender, manager counts.
     */
    public function getCoverageForDates(array $dates): array
    {
        if (empty($dates)) {
            return [];
        }

        $shifts = Shift::with('user')
            ->whereIn('date', $dates)
            ->get()
            ->groupBy('date');

        $result = [];
        foreach ($dates as $d) {
            $dayShifts = $shifts->get($d, collect());
            $result[$d] = [
                'chef'      => $dayShifts->where('user.role', UserRole::Chef)->count(),
                'waiter'    => $dayShifts->where('user.role', UserRole::Waiter)->count(),
                'bartender' => $dayShifts->where('user.role', UserRole::Bartender)->count(),
                'manager'   => $dayShifts->where('user.role', UserRole::Manager)->count(),
            ];
        }

        return $result;
    }
}
