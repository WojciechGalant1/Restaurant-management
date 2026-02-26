<?php

namespace Database\Factories;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => BillStatus::Open,
            'total_amount' => $this->faker->randomFloat(2, 20, 200),
            'tip_amount' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BillStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
