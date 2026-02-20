<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'invoice_number' => $this->faker->unique()->bothify('INV-####-????'),
            'amount' => $this->faker->randomFloat(2, 20, 200),
            'tax_id' => $this->faker->optional()->numerify('##########'),
            'customer_name' => $this->faker->optional()->name(),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'online']),
            'issued_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
