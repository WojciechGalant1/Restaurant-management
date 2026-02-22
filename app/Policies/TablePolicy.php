<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Table;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TablePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Host]);
    }

    public function view(User $user, Table $table): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function update(User $user, Table $table): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function delete(User $user, Table $table): bool
    {
        return $user->role === UserRole::Manager;
    }

    /**
     * Whether the user can change table status only (e.g. Host seating guests).
     */
    public function updateStatus(User $user, Table $table): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Host]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Table $table): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Table $table): bool
    {
        //
    }
}
