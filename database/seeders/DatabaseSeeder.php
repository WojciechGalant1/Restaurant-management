<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Table;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create initial users for testing
        User::create([
            'first_name' => 'John',
            'last_name' => 'Manager',
            'email' => 'manager@restaurant.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Waiter',
            'email' => 'waiter@restaurant.com',
            'password' => Hash::make('password'),
            'role' => 'waiter',
        ]);

        User::create([
            'first_name' => 'Chef',
            'last_name' => 'Master',
            'email' => 'chef@restaurant.com',
            'password' => Hash::make('password'),
            'role' => 'chef',
        ]);

        // Create some tables
        for ($i = 1; $i <= 10; $i++) {
            Table::create([
                'table_number' => $i,
                'capacity' => rand(2, 6),
                'status' => 'available',
            ]);
        }
        $this->call([
            DishSeeder::class,
        ]);
    }
}
