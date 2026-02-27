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
        return in_array($user->role, [UserRole::Manager, UserRole::Host, UserRole::Waiter]);
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
     * Table status is normally updated by domain services (reservations, orders).
     * This policy is reserved for any future internal/admin use.
     */
    public function updateStatus(User $user, Table $table): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Host], true);
    }

    /**
     * Allow waiter to mark their assigned table as cleaned (Cleaning → Available).
     * Manager and Host can always complete cleaning.
     */
    public function completeCleaning(User $user, Table $table): bool
    {
        if (in_array($user->role, [UserRole::Manager, UserRole::Host], true)) {
            return true;
        }
        if ($user->role === UserRole::Waiter) {
            $assignment = $table->activeAssignment;
            return $assignment && $assignment->user_id === $user->id;
        }
        return false;
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
