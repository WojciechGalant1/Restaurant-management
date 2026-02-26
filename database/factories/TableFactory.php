<?php

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\Table;
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
            'status' => TableStatus::Available,
        ];
    }
}
