<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\OrderItemStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(OrderItemStatus::cases());
        $createdAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'order_id' => Order::factory(),
            'menu_item_id' => MenuItem::factory(),
            'quantity' => $this->faker->numberBetween(1, 3),
            'unit_price' => $this->faker->randomFloat(2, 5, 50), // Ideally should match MenuItem price
            'notes' => $this->faker->optional(0.2)->sentence(),
            'status' => $status,
            'created_at' => $createdAt,
            'ready_at' => in_array($status, [OrderItemStatus::Ready, OrderItemStatus::Served]) 
                ? Carbon::parse($createdAt)->addMinutes(rand(5, 45)) 
                : null,
        ];
    }
}
