<?php

namespace App\Models;

use App\Enums\BillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'total_amount',
        'tip_amount',
        'paid_at',
    ];

    protected $casts = [
        'status' => BillStatus::class,
        'total_amount' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function isFullyPaid(): bool
    {
        return $this->totalPaid() >= (float) $this->total_amount;
    }
}
