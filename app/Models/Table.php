<?php

namespace App\Models;

use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use App\Events\TableStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'capacity',
        'status',
        'room_id',
        'sort_order',
    ];

    protected $casts = [
        'status' => TableStatus::class,
        'sort_order' => 'integer',
    ];

    // --- Relationships ---

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function assignments()
    {
        return $this->hasMany(TableAssignment::class);
    }

    /**
     * The active assignment (shift currently in progress).
     */
    public function activeAssignment()
    {
        return $this->hasOne(TableAssignment::class)
            ->whereHas('shift', fn ($q) => $q->activeNow());
    }

    // --- Accessors ---

    /**
     * The waiter currently assigned via an active shift, or null.
     */
    public function getCurrentWaiterAttribute(): ?User
    {
        $assignment = $this->relationLoaded('activeAssignment')
            ? $this->activeAssignment
            : $this->activeAssignment()->with('user')->first();

        return $assignment?->user;
    }

    public function getIsOccupiedAttribute(): bool
    {
        return $this->orders()
            ->where('status', OrderStatus::Open)
            ->exists();
    }

    // --- Domain methods ---

    public function assignTo(User $waiter, Shift $shift): TableAssignment
    {
        return TableAssignment::updateOrCreate(
            ['table_id' => $this->id, 'shift_id' => $shift->id],
            ['user_id' => $waiter->id, 'assigned_at' => now()]
        );
    }

    public function unassignFromShift(Shift $shift): void
    {
        $this->assignments()->where('shift_id', $shift->id)->delete();
    }

    /**
     * Status is updated by domain services (ReservationService, OrderService, InvoiceService).
     * Do not call from controllers; use reservation/order actions instead.
     */
    public function markAsOccupied(): void
    {
        $this->update(['status' => TableStatus::Occupied]);
        event(new TableStatusUpdated($this));
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => TableStatus::Available]);
        event(new TableStatusUpdated($this));
    }

    public function markAsReserved(): void
    {
        $this->update(['status' => TableStatus::Reserved]);
        event(new TableStatusUpdated($this));
    }

    public function markAsCleaning(): void
    {
        $this->update(['status' => TableStatus::Cleaning]);
        event(new TableStatusUpdated($this));
    }

    // --- Scopes ---

    /**
     * Filter tables to only those assigned to $user via an active shift.
     * Managers and Hosts see all tables.
     */
    public function scopeForWaiter($query, User $user)
    {
        if (in_array($user->role, [UserRole::Manager, UserRole::Host])) {
            return $query;
        }

        return $query->whereHas('assignments', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->whereHas('shift', fn ($sq) => $sq->activeNow());
        });
    }
}
