<?php

namespace App\Models;

use App\Enums\TableStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'capacity',
        'status',
        'waiter_id',
    ];

    protected $casts = [
        'status' => TableStatus::class,
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    // Domain methods
    public function assignTo(User $waiter): void
    {
        $this->update(['waiter_id' => $waiter->id]);
    }

    public function markAsOccupied(): void
    {
        $this->update(['status' => TableStatus::Occupied]);
    }

    public function markAsAvailable(): void
    {
        $this->update([
            'status' => TableStatus::Available,
            'waiter_id' => null,
        ]);
    }

    // Scopes
    public function scopeForWaiter($query, User $user)
    {
        if ($user->role === 'manager') {
            return $query;
        }

        return $query->where('waiter_id', $user->id);
    }

    // Accessors
    public function getIsOccupiedAttribute(): bool
    {
        return $this->orders()
            ->whereIn('status', ['pending', 'preparing'])
            ->exists();
    }
}
