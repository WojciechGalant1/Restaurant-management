<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Waiter]);
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Waiter]);
    }

    /**
     * Whether the user can update this reservation in the waiter context (e.g. mark seated, no-show).
     * Manager: always. Waiter: only if the reservation's table is assigned to them via an active shift.
     */
    public function updateAsWaiter(User $user, Reservation $reservation): bool
    {
        if ($user->role === UserRole::Manager) {
            return true;
        }
        if ($user->role !== UserRole::Waiter) {
            return false;
        }
        $reservation->loadMissing('table');
        if (!$reservation->table) {
            return false;
        }
        $currentWaiter = $reservation->table->currentWaiter;
        return $currentWaiter && $currentWaiter->id === $user->id;
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->role === UserRole::Manager;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservation $reservation): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        //
    }
}
