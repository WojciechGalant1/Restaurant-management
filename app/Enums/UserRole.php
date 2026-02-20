<?php

namespace App\Enums;

enum UserRole: string
{
    case Manager = 'manager';
    case Chef = 'chef';
    case Waiter = 'waiter';
    case Bartender = 'bartender';

    public function label(): string
    {
        return match($this) {
            self::Manager => 'Manager',
            self::Chef => 'Chef',
            self::Waiter => 'Waiter',
            self::Bartender => 'Bartender',
        };
    }

    public function visibleCategories(): array
    {
        return match($this) {
            self::Chef => [
                DishCategory::Starter,
                DishCategory::Main,
                DishCategory::Dessert,
                DishCategory::Side,
            ],
            self::Bartender => [
                DishCategory::Drink,
            ],
            self::Manager => DishCategory::cases(),
            default => [],
        };
    }
}
