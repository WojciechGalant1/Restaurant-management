<?php

namespace Database\Factories;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Invoice;
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
            'bill_id' => Bill::factory(),
            'invoice_number' => $this->faker->unique()->bothify('INV-####-????'),
            'amount' => $this->faker->randomFloat(2, 20, 200),
            'tax_id' => $this->faker->optional()->numerify('##########'),
            'customer_name' => $this->faker->optional()->name(),
            'issued_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
