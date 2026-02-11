<?php

namespace App\Policies;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DishPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dish $dish): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function update(User $user, Dish $dish): bool
    {
        return $user->role === 'manager';
    }

    public function delete(User $user, Dish $dish): bool
    {
        return $user->role === 'manager';
    }
}
