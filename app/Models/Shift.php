<?php

namespace App\Models;

use App\Enums\ShiftType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Represents a scheduled work shift.
 *
 * The `date` field represents the "business date" — the day the shift starts.
 * For shifts crossing midnight (e.g. Night shift 22:00–06:00),
 * the date is still the day the shift begins, but the shift extends into the next calendar day.
 *
 * The `shift_type` field is a category/label (Morning, Evening, FullDay).
 * The `start_time` and `end_time` fields are the authoritative time boundaries.
 */
class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'shift_type',
        'start_time',
        'end_time',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
        'shift_type' => ShiftType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tableAssignments()
    {
        return $this->hasMany(TableAssignment::class);
    }

    public function clockIns()
    {
        return $this->hasMany(ShiftClockIn::class);
    }

    public function crossesMidnight(): bool
    {
        return $this->end_time < $this->start_time;
    }

    public function durationInHours(): float
    {
        $date = $this->date->format('Y-m-d');
        $start = Carbon::parse($date . ' ' . $this->start_time);
        $end = Carbon::parse($date . ' ' . $this->end_time);
        if ($end <= $start) {
            $end->addDay();
        }
        return round($start->diffInMinutes($end) / 60, 2);
    }

    /**
     * Whether this shift is currently active.
     */
    public function isActive(): bool
    {
        $today = today()->toDateString();
        $yesterday = today()->subDay()->toDateString();
        $now = now()->format('H:i:s');
        $shiftDate = $this->date->format('Y-m-d');

        if (!$this->crossesMidnight()) {
            // Normal shift: active if today and within time range
            return $shiftDate === $today
                && $now >= $this->start_time
                && $now <= $this->end_time;
        }

        // Midnight-crossing shift:
        // Active if started today and we're past start_time,
        // OR started yesterday and we're before end_time
        return ($shiftDate === $today && $now >= $this->start_time)
            || ($shiftDate === $yesterday && $now <= $this->end_time);
    }

    /**
     * Scope: shifts that are currently active.
     * Handles normal shifts and shifts crossing midnight.
     */
    public function scopeActiveNow(Builder $query): Builder
    {
        $today = today()->toDateString();
        $yesterday = today()->subDay()->toDateString();
        $now = now()->format('H:i:s');

        return $query->where(function (Builder $q) use ($today, $yesterday, $now) {
            // Case 1: Normal shift (start <= end), today, within range
            $q->where(function (Builder $q) use ($today, $now) {
                $q->where('date', $today)
                  ->whereColumn('start_time', '<=', 'end_time')
                  ->where('start_time', '<=', $now)
                  ->where('end_time', '>=', $now);
            })
            // Case 2: Midnight-crossing shift started today (past start_time)
            ->orWhere(function (Builder $q) use ($today, $now) {
                $q->where('date', $today)
                  ->whereColumn('start_time', '>', 'end_time')
                  ->where('start_time', '<=', $now);
            })
            // Case 3: Midnight-crossing shift started yesterday (before end_time)
            ->orWhere(function (Builder $q) use ($yesterday, $now) {
                $q->where('date', $yesterday)
                  ->whereColumn('start_time', '>', 'end_time')
                  ->where('end_time', '>=', $now);
            });
        });
    }

    /**
     * Scope: shifts active at a specific date and time.
     */
    public function scopeActiveAt(Builder $query, string $date, string $time): Builder
    {
        $previousDay = Carbon::parse($date)->subDay()->toDateString();

        return $query->where(function (Builder $q) use ($date, $previousDay, $time) {
            $q->where(function (Builder $q) use ($date, $time) {
                $q->where('date', $date)
                  ->whereColumn('start_time', '<=', 'end_time')
                  ->where('start_time', '<=', $time)
                  ->where('end_time', '>=', $time);
            })
            ->orWhere(function (Builder $q) use ($date, $time) {
                $q->where('date', $date)
                  ->whereColumn('start_time', '>', 'end_time')
                  ->where('start_time', '<=', $time);
            })
            ->orWhere(function (Builder $q) use ($previousDay, $time) {
                $q->where('date', $previousDay)
                  ->whereColumn('start_time', '>', 'end_time')
                  ->where('end_time', '>=', $time);
            });
        });
    }
}
