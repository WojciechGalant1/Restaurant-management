<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\MenuItem;
use App\Enums\DishCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dish_id' => Dish::factory(),
            'price' => function (array $attributes) {
                $dish = Dish::find($attributes['dish_id']);
                $priceRanges = [
                    DishCategory::Starter->value => [12, 28],
                    DishCategory::Main->value => [28, 79],
                    DishCategory::Dessert->value => [14, 32],
                    DishCategory::Drink->value => [8, 26],
                    DishCategory::Side->value => [12, 28],
                ];
                
                $range = $priceRanges[$dish->category->value] ?? [10, 50];
                return fake()->randomFloat(2, $range[0], $range[1]);
            },
            'is_available' => $this->faker->boolean(90),
        ];
    }
}
