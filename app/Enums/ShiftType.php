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
}
