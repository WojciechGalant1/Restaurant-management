<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'shift_id',
        'user_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Assignments whose shift is currently active.
     */
    public function scopeActiveNow(Builder $query): Builder
    {
        return $query->whereHas('shift', fn (Builder $q) => $q->activeNow());
    }

    public function scopeForShift(Builder $query, int $shiftId): Builder
    {
        return $query->where('shift_id', $shiftId);
    }
}
