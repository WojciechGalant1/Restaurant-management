<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function create(User $user): bool
    {
        return true; // Waiters can create invoices (at least to settle orders)
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Manager;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Manager;
    }
}
