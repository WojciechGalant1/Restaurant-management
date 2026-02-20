<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'table_number' => $this->faker->unique()->numberBetween(1, 100),
            'capacity' => $this->faker->numberBetween(2, 8),
            'status' => $this->faker->randomElement(['available', 'occupied', 'reserved']),
            'waiter_id' => User::factory(), // Creates a new user if not provided
        ];
    }
}
