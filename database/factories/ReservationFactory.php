<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'table_id' => Table::factory(),
            'customer_name' => $this->faker->name(),
            'phone_number' => $this->faker->phoneNumber(),
            'reservation_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'reservation_time' => $this->faker->time('H:i'),
            'party_size' => $this->faker->numberBetween(2, 10),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show']),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
