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
            'first_name' => 'Mark',
            'last_name' => 'Waiter',
            'email' => 'waiter2@restaurant.com',
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

        $this->call([
            TableSeeder::class,
            DishSeeder::class,
            ReservationSeeder::class,
            OrderSeeder::class,
            ShiftSeeder::class,
        ]);
    }
}
