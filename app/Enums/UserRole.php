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

    /**
     * Which dashboard sections to show for this role.
     */
    public function dashboardSections(): array
    {
        return match ($this) {
            self::Manager => [
                'kpis' => true,
                'charts' => true,
                'kitchen' => true,
                'staff' => true,
                'alerts' => true,
                'top_performers' => true,
                'live_feed' => true,
                'quick_actions' => true,
            ],
            self::Chef, self::Bartender => [
                'kpis' => true,
                'charts' => false,
                'kitchen' => true,
                'staff' => false,
                'alerts' => false,
                'top_performers' => false,
                'live_feed' => true,
                'quick_actions' => true,
            ],
            self::Waiter => [
                'kpis' => true,
                'charts' => false,
                'kitchen' => false,
                'staff' => false,
                'alerts' => false,
                'top_performers' => false,
                'live_feed' => true,
                'quick_actions' => true,
            ],
        };
    }
}
