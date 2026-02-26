<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $orderedAt = $this->faker->dateTimeBetween('-40 days', 'now');
        $isRecent = Carbon::parse($orderedAt)->diffInHours(now()) < 12;

        if ($isRecent) {
            $status = $this->faker->randomElement([
                ...array_fill(0, 60, OrderStatus::Open),
                ...array_fill(0, 35, OrderStatus::Paid),
                ...array_fill(0, 5, OrderStatus::Cancelled),
            ]);
        } else {
            $status = $this->faker->randomElement([
                ...array_fill(0, 95, OrderStatus::Paid),
                ...array_fill(0, 5, OrderStatus::Cancelled),
            ]);
        }

        return [
            'table_id' => Table::factory(),
            'user_id' => User::factory(),
            'status' => $status,
            'total_price' => 0, // Recalculated in seeder
            'ordered_at' => $orderedAt,
            'paid_at' => $status === OrderStatus::Paid 
                ? Carbon::parse($orderedAt)->addMinutes(rand(30, 120)) 
                : null,
        ];
    }
}
