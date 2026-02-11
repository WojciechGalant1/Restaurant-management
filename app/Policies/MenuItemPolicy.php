<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MenuItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MenuItem $menuItem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function update(User $user, MenuItem $menuItem): bool
    {
        return $user->role === 'manager';
    }

    public function delete(User $user, MenuItem $menuItem): bool
    {
        return $user->role === 'manager';
    }
}
