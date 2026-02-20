<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'table_id' => Table::factory(),
            'user_id' => User::factory(), // This usually refers to the waiter
            'status' => $this->faker->randomElement(OrderStatus::cases()),
            'total_price' => $this->faker->randomFloat(2, 20, 200),
            'ordered_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'paid_at' => function (array $attributes) {
                return $attributes['status'] === OrderStatus::Paid ? $this->faker->dateTimeBetween($attributes['ordered_at'], 'now') : null;
            },
        ];
    }
}
