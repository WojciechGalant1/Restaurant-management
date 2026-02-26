<?php

namespace Database\Factories;

use App\Enums\DishCategory;
use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dish>
 */
class DishFactory extends Factory
{
    protected $model = Dish::class;

    public function definition(): array
    {
        $dishes = [
            DishCategory::Starter->value => [
                'Caesar Salad', 'Bruschetta', 'Garlic Bread', 'Calamari', 'Tomato Soup',
                'Caprese Salad', 'Chicken Wings', 'Spring Rolls', 'Nachos', 'Mozzarella Sticks'
            ],
            DishCategory::Main->value => [
                'Grilled Salmon', 'Steak Frites', 'Spaghetti Carbonara', 'Chicken Curry', 'Beef Burger',
                'Fish and Chips', 'Mushroom Risotto', 'Pizza Margherita', 'Roast Chicken', 'Lamb Chops'
            ],
            DishCategory::Dessert->value => [
                'Tiramisu', 'New York Cheesecake', 'Chocolate Brownie', 'Apple Pie', 'Ice Cream Sundae',
                'Panna Cotta', 'Creme Brulee', 'Fruit Salad', 'Belgian Waffles', 'Pancakes'
            ],
            DishCategory::Drink->value => [
                'Cola', 'Lemonade', 'Iced Tea', 'Espresso', 'Cappuccino',
                'Orange Juice', 'Sparkling Water', 'Craft Beer', 'House Red Wine', 'Mojito'
            ],
            DishCategory::Side->value => [
                'French Fries', 'Mashed Potatoes', 'Steamed Vegetables', 'Onion Rings', 'Jasmine Rice',
                'Coleslaw', 'Roasted Potatoes', 'Garlic Mushrooms', 'Garden Salad', 'Sweet Potato Fries'
            ],
        ];

        $category = $this->faker->randomElement(DishCategory::cases());
        $name = $this->faker->randomElement($dishes[$category->value]);

        return [
            'name' => $name,
            'description' => $this->faker->sentence(10) . ' Delicious ' . strtolower($name) . ' prepared with fresh ingredients.',
            'category' => $category->value,
        ];
    }
}
