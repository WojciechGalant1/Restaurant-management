<?php

namespace App\Models;

use App\Enums\DishCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
    ];

    protected $casts = [
        'category' => DishCategory::class,
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}
