<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Room;
use App\Models\Shift;
use App\Models\Table;
use App\Models\TableAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            'room',
        ])->orderBy('sort_order')->orderBy('table_number')->get();

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
                'room_id' => $table->room_id,
                'room_name' => $table->room?->name,
                'room_color' => $table->room?->color,
            ];
        })->toArray();
    }

    /**
     * Reorder rooms and table assignments (room_id, sort_order) from drag-and-drop payload.
     */
    public function reorderRoomsAndTables(array $validated): void
    {
        DB::transaction(function () use ($validated) {
            foreach ($validated['rooms'] as $roomData) {
                Room::where('id', $roomData['id'])->update(['sort_order' => $roomData['sort_order']]);

                foreach ($roomData['tables'] as $tableData) {
                    Table::where('id', $tableData['id'])->update([
                        'room_id' => $roomData['id'],
                        'sort_order' => $tableData['sort_order'],
                    ]);
                }
            }

            foreach ($validated['unassigned'] as $tableData) {
                Table::where('id', $tableData['id'])->update([
                    'room_id' => null,
                    'sort_order' => $tableData['sort_order'],
                ]);
            }
        });
    }
}
