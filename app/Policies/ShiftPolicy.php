<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Manager,
            UserRole::Host,
            UserRole::Waiter,
            UserRole::Chef,
            UserRole::Bartender,
        ]);
    }

    public function view(User $user, Shift $shift): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Host]) || $user->id === $shift->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $user->role === UserRole::Manager;
    }
}
