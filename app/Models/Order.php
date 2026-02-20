<?php

namespace App\Models;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => \App\Enums\OrderStatus::class,
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
}
