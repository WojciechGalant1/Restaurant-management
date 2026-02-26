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
        $status = $this->faker->randomElement([
            ...array_fill(0, 65, OrderItemStatus::Served),
            ...array_fill(0, 15, OrderItemStatus::Ready),
            ...array_fill(0, 10, OrderItemStatus::Preparing),
            ...array_fill(0, 8, OrderItemStatus::Pending),
            ...array_fill(0, 2, OrderItemStatus::Cancelled),
        ]);

        $quantity = $this->faker->randomElement([
            ...array_fill(0, 70, 1),
            ...array_fill(0, 20, 2),
            ...array_fill(0, 10, 3),
        ]);

        return [
            'order_id' => Order::factory(),
            'menu_item_id' => MenuItem::factory(),
            'quantity' => $quantity,
            'unit_price' => function (array $attributes) {
                return MenuItem::find($attributes['menu_item_id'])->price;
            },
            'notes' => $this->faker->optional(0.1)->sentence(),
            'status' => $status,
            'created_at' => now(), // Usually overridden in seeder
            'ready_at' => in_array($status, [OrderItemStatus::Ready, OrderItemStatus::Served]) 
                ? now()->addMinutes(rand(5, 45)) 
                : null,
        ];
    }
}
