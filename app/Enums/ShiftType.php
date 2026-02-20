<?php

namespace App\Enums;

enum ShiftType: string
{
    case Morning = 'morning';
    case Evening = 'evening';
    case FullDay = 'full_day';

    public function label(): string
    {
        return match($this) {
            self::Morning => 'Morning',
            self::Evening => 'Evening',
            self::FullDay => 'Full Day',
        };
    }

    public function hours(): array
    {
        return match($this) {
            self::Morning => ['start' => '08:00', 'end' => '16:00'],
            self::Evening => ['start' => '16:00', 'end' => '24:00'],
            self::FullDay  => ['start' => '08:00', 'end' => '24:00'],
        };
    }

    public function startTime(): string
    {
        return $this->hours()['start'];
    }

    public function endTime(): string
    {
        return $this->hours()['end'];
    }

    /**
     * Check if the given time (HH:MM) falls within this shift.
     * Handles shifts that cross midnight (e.g. 22:00–06:00).
     */
    public function isActiveAt(string $time): bool
    {
        $start = $this->startTime();
        $end = $this->endTime();

        if ($end <= $start) {
            // Shift crosses midnight (e.g. 22:00–06:00)
            return $time >= $start || $time <= $end;
        }

        return $time >= $start && $time <= $end;
    }
}
