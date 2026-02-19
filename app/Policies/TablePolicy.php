<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function view(User $user, Table $table): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function update(User $user, Table $table): bool
    {
        return $user->role === 'manager';
    }

    public function delete(User $user, Table $table): bool
    {
        return $user->role === 'manager';
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
