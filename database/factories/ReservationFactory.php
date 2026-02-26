<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
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
            'duration_minutes' => $this->faker->randomElement([60, 90, 120, 150, 180]),
            'status' => $this->faker->randomElement([
                ReservationStatus::Pending,
                ReservationStatus::Confirmed,
                ReservationStatus::Seated,
                ReservationStatus::Completed,
                ReservationStatus::Cancelled,
                ReservationStatus::NoShow,
            ]),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
