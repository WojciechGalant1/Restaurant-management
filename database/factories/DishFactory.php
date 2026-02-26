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
                'names' => ['Caesar Salad', 'Bruschetta', 'Garlic Bread', 'Calamari', 'Tomato Soup', 'Caprese Salad', 'Chicken Wings', 'Spring Rolls', 'Nachos', 'Mozzarella Sticks'],
                'price_range' => [12, 28]
            ],
            DishCategory::Main->value => [
                'names' => ['Grilled Salmon', 'Steak Frites', 'Spaghetti Carbonara', 'Chicken Curry', 'Beef Burger', 'Fish and Chips', 'Mushroom Risotto', 'Pizza Margherita', 'Roast Chicken', 'Lamb Chops'],
                'price_range' => [28, 79]
            ],
            DishCategory::Dessert->value => [
                'names' => ['Tiramisu', 'New York Cheesecake', 'Chocolate Brownie', 'Apple Pie', 'Ice Cream Sundae', 'Panna Cotta', 'Creme Brulee', 'Fruit Salad', 'Belgian Waffles', 'Pancakes'],
                'price_range' => [14, 32]
            ],
            DishCategory::Drink->value => [
                'names' => ['Cola', 'Lemonade', 'Iced Tea', 'Espresso', 'Cappuccino', 'Orange Juice', 'Sparkling Water', 'Craft Beer', 'House Red Wine', 'Mojito'],
                'price_range' => [8, 26]
            ],
            DishCategory::Side->value => [
                'names' => ['French Fries', 'Mashed Potatoes', 'Steamed Vegetables', 'Onion Rings', 'Jasmine Rice', 'Coleslaw', 'Roasted Potatoes', 'Garlic Mushrooms', 'Garden Salad', 'Sweet Potato Fries'],
                'price_range' => [12, 28]
            ],
        ];

        $category = $this->faker->randomElement(DishCategory::cases());
        $config = $dishes[$category->value];
        $name = $this->faker->randomElement($config['names']);

        return [
            'name' => $name,
            'description' => $this->faker->sentence(10) . ' Delicious ' . strtolower($name) . ' prepared with fresh ingredients.',
            'category' => $category->value,
        ];
    }
}
