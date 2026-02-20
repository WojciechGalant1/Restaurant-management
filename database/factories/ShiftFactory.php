<?php

namespace Database\Factories;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'shift_type' => $this->faker->randomElement(['morning', 'evening']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
