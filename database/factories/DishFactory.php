<?php

namespace Database\Factories;

use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dish>
 */
class DishFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dish::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dishes = [
            'starter' => [
                'Caesar Salad', 'Bruschetta', 'Garlic Bread', 'Calamari', 'Tomato Soup',
                'Caprese Salad', 'Chicken Wings', 'Spring Rolls', 'Nachos', 'Mozzarella Sticks'
            ],
            'main' => [
                'Grilled Salmon', 'Steak Frites', 'Spaghetti Carbonara', 'Chicken Curry', 'Beef Burger',
                'Fish and Chips', 'Mushroom Risotto', 'Pizza Margherita', 'Roast Chicken', 'Lamb Chops'
            ],
            'dessert' => [
                'Tiramisu', 'New York Cheesecake', 'Chocolate Brownie', 'Apple Pie', 'Ice Cream Sundae',
                'Panna Cotta', 'Creme Brulee', 'Fruit Salad', 'Belgian Waffles', 'Pancakes'
            ],
            'drink' => [
                'Cola', 'Lemonade', 'Iced Tea', 'Espresso', 'Cappuccino',
                'Orange Juice', 'Sparkling Water', 'Craft Beer', 'House Red Wine', 'Mojito'
            ],
            'side' => [
                'French Fries', 'Mashed Potatoes', 'Steamed Vegetables', 'Onion Rings', 'Jasmine Rice',
                'Coleslaw', 'Roasted Potatoes', 'Garlic Mushrooms', 'Garden Salad', 'Sweet Potato Fries'
            ]
        ];

        $category = $this->faker->randomElement(array_keys($dishes));
        $name = $this->faker->randomElement($dishes[$category]);

        return [
            'name' => $name,
            'description' => $this->faker->sentence(10) . ' Delicious ' . strtolower($name) . ' prepared with fresh ingredients.',
            'category' => $category,
        ];
    }
}
