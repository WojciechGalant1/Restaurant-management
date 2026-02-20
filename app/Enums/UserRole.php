<?php

namespace App\Enums;

enum UserRole: string
{
    case Manager = 'manager';
    case Chef = 'chef';
    case Waiter = 'waiter';

    public function label(): string
    {
        return match($this) {
            self::Manager => 'Manager',
            self::Chef => 'Chef',
            self::Waiter => 'Waiter',
        };
    }
}
