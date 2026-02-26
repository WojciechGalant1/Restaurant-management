<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftClockIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'user_id',
        'clocked_in_at',
    ];

    protected $casts = [
        'clocked_in_at' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
