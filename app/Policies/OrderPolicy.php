<?php

namespace App\Policies;

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
        return in_array($user->role, ['manager', 'waiter']);
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->role === 'manager') return true;
        if ($user->role === 'chef') return in_array($order->status, ['pending', 'in_preparation']);
        if ($user->role === 'waiter') return $order->user_id === $user->id;
        
        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->role === 'manager';
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
