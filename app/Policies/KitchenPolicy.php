<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\OrderItem;
use App\Models\User;

class KitchenPolicy
{
    /**
     * Kitchen dashboard visibility.
     */
    public function view(User $user): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Chef, UserRole::Bartender]);
    }

    /**
     * Update status of order items from the kitchen screen.
     * User must have kitchen role and the item's dish category must be in their visible categories.
     */
    public function updateItemStatus(User $user, OrderItem $orderItem): bool
    {
        if (!in_array($user->role, [UserRole::Manager, UserRole::Chef, UserRole::Bartender])) {
            return false;
        }
        $orderItem->loadMissing('menuItem.dish');
        $dish = $orderItem->menuItem?->dish;
        if (!$dish) {
            return false;
        }
        return in_array($dish->category, $user->role->visibleCategories());
    }
}

