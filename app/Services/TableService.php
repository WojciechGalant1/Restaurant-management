<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Shift;
use App\Models\Table;
use App\Models\TableAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class TableService
{
    /**
     * Waiters currently on shift (for manager to assign to tables).
     */
    public function getAssignableWaiters(): Collection
    {
        $activeWaiterIds = Shift::activeNow()->pluck('user_id');

        return User::where('role', UserRole::Waiter)
            ->whereIn('id', $activeWaiterIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * Active shifts with their waiters (for the assignment dropdown).
     */
    public function getActiveWaiterShifts(): Collection
    {
        return Shift::activeNow()
            ->with('user')
            ->whereHas('user', fn ($q) => $q->where('role', UserRole::Waiter))
            ->get();
    }

    /**
     * Assign a table to a waiter for a specific shift.
     */
    public function assignTableToShift(Table $table, User $waiter, Shift $shift): TableAssignment
    {
        return $table->assignTo($waiter, $shift);
    }

    /**
     * Remove assignment for a table on a specific shift.
     */
    public function unassignTableFromShift(Table $table, Shift $shift): void
    {
        $table->unassignFromShift($shift);
    }

    /**
     * Floor data: all tables with active assignments, orders, reservations.
     */
    public function getFloorData(): array
    {
        $tables = Table::with([
            'activeAssignment.user',
            'activeAssignment.shift',
        ])->orderBy('table_number')->get();

        return $tables->map(function (Table $table) {
            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'capacity' => $table->capacity,
                'status' => $table->status->value,
                'status_label' => $table->status->label(),
                'is_occupied' => $table->is_occupied,
                'waiter_name' => $table->activeAssignment?->user?->name,
                'waiter_id' => $table->activeAssignment?->user_id,
                'shift_id' => $table->activeAssignment?->shift_id,
            ];
        })->toArray();
    }
}
