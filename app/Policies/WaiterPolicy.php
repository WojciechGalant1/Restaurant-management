<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;

class WaiterPolicy
{
    /**
     * Waiter dashboard visibility.
     */
    public function view(User $user): bool
    {
        return in_array($user->role, ['manager', 'waiter']);
    }

    /**
     * Mark an order item as served from waiter screen.
     */
    public function serveItem(User $user, OrderItem $orderItem): bool
    {
        // Role check only; status validation remains in controller/domain logic
        return in_array($user->role, ['manager', 'waiter']);
    }
}

