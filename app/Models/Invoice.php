<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_number',
        'amount',
        'tax_id',
        'customer_name',
        'payment_method',
        'issued_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
