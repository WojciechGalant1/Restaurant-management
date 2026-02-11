<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function view(User $user, Shift $shift): bool
    {
        return $user->role === 'manager' || $user->id === $shift->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->role === 'manager';
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $user->role === 'manager';
    }
}
