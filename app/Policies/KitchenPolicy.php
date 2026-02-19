<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;

class KitchenPolicy
{
    /**
     * Kitchen dashboard visibility.
     */
    public function view(User $user): bool
    {
        return in_array($user->role, ['manager', 'chef']);
    }

    /**
     * Update status of order items from the kitchen screen.
     */
    public function updateItemStatus(User $user, OrderItem $orderItem): bool
    {
        // Same role check for now; business rules can evolve here later
        return in_array($user->role, ['manager', 'chef']);
    }
}

