<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case WalkInSeated = 'walk_in_seated';
    case Seated = 'seated';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::WalkInSeated => 'Walk In Seated',
            self::Seated => 'Seated',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::NoShow => 'No Show',
        };
    }
}
