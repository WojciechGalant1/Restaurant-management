<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class DishSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 30 dishes, each with one menu item
        Dish::factory()
            ->count(30)
            ->has(MenuItem::factory()->state(function (array $attributes, Dish $dish) {
                return ['dish_id' => $dish->id];
            }))
            ->create();
    }
}
