<?php

namespace App\Models;

use App\Enums\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'shift_type',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
        'shift_type' => ShiftType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
