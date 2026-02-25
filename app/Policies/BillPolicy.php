<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Manager, UserRole::Waiter]);
    }

    public function view(User $user, Bill $bill): bool
    {
        if ($user->role === UserRole::Manager) {
            return true;
        }

        if ($user->role === UserRole::Waiter) {
            $table = $bill->order?->table;
            if (!$table) {
                return false;
            }
            return $table->assignments()
                ->where('user_id', $user->id)
                ->whereHas('shift', fn ($q) => $q->activeNow())
                ->exists();
        }

        return false;
    }

    public function addPayment(User $user, Bill $bill): bool
    {
        return $this->view($user, $bill);
    }

    public function cancel(User $user, Bill $bill): bool
    {
        return $this->addPayment($user, $bill);
    }
}
