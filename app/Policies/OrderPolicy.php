<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated employees can see orders
    }

    public function view(User $user, Order $order): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Waiter]);
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->role === UserRole::Manager) return true;
        if (in_array($user->role, [UserRole::Chef, UserRole::Bartender])) return $order->status === OrderStatus::Open;
        if ($user->role === UserRole::Waiter) {
            if ($order->user_id === $user->id) {
                return true;
            }

            // Allow taking over an unclaimed (user_id NULL) open order
            // if the table is assigned to this waiter via an active shift.
            if ($order->status !== OrderStatus::Open) {
                return false;
            }

            if ($order->user_id !== null) {
                return false;
            }

            $table = $order->table;
            if (!$table) {
                return false;
            }

            return $table->assignments()
                ->where('user_id', $user->id)
                ->whereHas('shift', fn ($q) => $q->activeNow())
                ->exists();
        }
        
        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->role === UserRole::Manager;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        //
    }
}
