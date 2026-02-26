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
            'party_size' => $this->faker->randomElement([
                ...array_fill(0, 10, 1),
                ...array_fill(0, 30, 2),
                ...array_fill(0, 25, 3),
                ...array_fill(0, 20, 4),
                ...array_fill(0, 5, 5),
                ...array_fill(0, 5, 6),
                ...array_fill(0, 3, 7),
                ...array_fill(0, 2, 8),
            ]),
            'duration_minutes' => $this->faker->randomElement([
                ...array_fill(0, 70, $this->faker->numberBetween(60, 180)),
                ...array_fill(0, 20, $this->faker->numberBetween(15, 60)),
                ...array_fill(0, 8, $this->faker->numberBetween(240, 480)),
                ...array_fill(0, 2, 480),
            ]),
            'status' => $this->faker->randomElement([
                ...array_fill(0, 45, ReservationStatus::Completed),
                ...array_fill(0, 20, ReservationStatus::Confirmed),
                ...array_fill(0, 10, ReservationStatus::Seated),
                ...array_fill(0, 12, ReservationStatus::Pending),
                ...array_fill(0, 8, ReservationStatus::WalkInSeated),
                ...array_fill(0, 3, ReservationStatus::Cancelled),
                ...array_fill(0, 2, ReservationStatus::NoShow),
            ]),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
