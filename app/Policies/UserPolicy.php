<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === UserRole::Manager || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === UserRole::Manager || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Prevent deleting yourself
        return $user->role === UserRole::Manager && $user->id !== $model->id;
    }
}
