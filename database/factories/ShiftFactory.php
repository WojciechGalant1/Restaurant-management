<?php

namespace Database\Factories;

use App\Enums\ShiftType;
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
        $type = $this->faker->randomElement(ShiftType::cases());

        return [
            'user_id' => User::factory(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'shift_type' => $type->value,
            'start_time' => $type->startTime(),
            'end_time' => $type->endTime(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
