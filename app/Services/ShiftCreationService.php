<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftCreationService
{
    /**
     * Resolve list of dates: either [date] or weekdays in the week of date.
     */
    public function resolveDates(string $baseDate, array $replicateDays): array
    {
        $base = Carbon::parse($baseDate);
        if (empty($replicateDays)) {
            return [$base->toDateString()];
        }
        $weekStart = $base->copy()->startOfWeek(Carbon::MONDAY);
        $dates = [];
        foreach ($replicateDays as $d) {
            $d = (int) $d;
            if ($d >= 1 && $d <= 7) {
                $day = $weekStart->copy()->addDays($d - 1);
                $dates[] = $day->toDateString();
            }
        }
        $dates = array_unique($dates);
        sort($dates);
        return $dates;
    }

    /**
     * Normalize user_ids from request (array or comma-separated string).
     */
    public function normalizeUserIds($userIds): array
    {
        if (is_string($userIds)) {
            $userIds = array_filter(explode(',', $userIds));
        }
        $userIds = $userIds ?: [];
        return array_values(array_unique(array_map('intval', $userIds)));
    }

    /**
     * Check if two time ranges overlap (handles midnight-crossing).
     * Each range is given as date (Y-m-d) + start_time + end_time (H:i or H:i:s).
     */
    public function hasTimeOverlap(
        string $dateA,
        string $startA,
        string $endA,
        string $dateB,
        string $startB,
        string $endB
    ): bool {
        $rangeA = $this->toDateTimeRange($dateA, $startA, $endA);
        $rangeB = $this->toDateTimeRange($dateB, $startB, $endB);
        return $rangeA['start']->lt($rangeB['end']) && $rangeB['start']->lt($rangeA['end']);
    }

    /**
     * Convert date + start_time + end_time to Carbon start/end (end may be next day if crosses midnight).
     */
    private function toDateTimeRange(string $date, string $startTime, string $endTime): array
    {
        $start = Carbon::parse($date . ' ' . $startTime);
        $end = Carbon::parse($date . ' ' . $endTime);
        if ($end->lte($start)) {
            $end = $end->copy()->addDay();
        }
        return ['start' => $start, 'end' => $end];
    }

    /**
     * Find first conflict: user already has a shift on one of the dates that overlaps in time.
     * Returns ['user_id' => int, 'user_name' => string, 'date' => string] or null.
     */
    public function findConflict(
        array $userIds,
        array $dates,
        string $shiftType,
        string $startTime,
        string $endTime
    ): ?array {
        foreach ($userIds as $userId) {
            foreach ($dates as $dateStr) {
                $existing = Shift::where('user_id', $userId)
                    ->where('date', $dateStr)
                    ->get();
                foreach ($existing as $shift) {
                    $shiftDate = $shift->date->format('Y-m-d');
                    $st = $shift->start_time;
                    $et = $shift->end_time;
                    if (is_object($st)) {
                        $st = $st->format('H:i:s');
                    }
                    if (is_object($et)) {
                        $et = $et->format('H:i:s');
                    }
                    if ($this->hasTimeOverlap($dateStr, $startTime, $endTime, $shiftDate, $st, $et)) {
                        $user = User::find($userId);
                        $name = $user ? trim($user->first_name . ' ' . $user->last_name) : (string) $userId;
                        return ['user_id' => $userId, 'user_name' => $name, 'date' => $dateStr];
                    }
                }
            }
        }
        return null;
    }

    /**
     * Create multiple shifts (user_id × date). Returns count created.
     */
    public function createMultipleShifts(array $userIds, array $dates, array $shiftData): int
    {
        $created = 0;
        DB::transaction(function () use ($userIds, $dates, $shiftData, &$created) {
            foreach ($userIds as $userId) {
                foreach ($dates as $dateStr) {
                    Shift::create([
                        'user_id'    => $userId,
                        'date'       => $dateStr,
                        'shift_type' => $shiftData['shift_type'],
                        'start_time' => $shiftData['start_time'],
                        'end_time'   => $shiftData['end_time'],
                        'notes'      => $shiftData['notes'] ?? null,
                    ]);
                    $created++;
                }
            }
        });
        return $created;
    }
}
