<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'user_id',
        'status',
        'total_price',
        'ordered_at',
        'paid_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'ordered_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function scopeForWaiter($query, User $user)
    {
        if ($user->role === UserRole::Manager) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function openBill(): ?Bill
    {
        return $this->bills()->where('status', \App\Enums\BillStatus::Open)->first();
    }

    public function paidBill(): ?Bill
    {
        return $this->bills()->where('status', \App\Enums\BillStatus::Paid)->latest()->first();
    }
}
