<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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

        User::create([
            'first_name' => 'Bartender',
            'last_name' => 'Expert',
            'email' => 'bartender@restaurant.com',
            'password' => Hash::make('password'),
            'role' => 'bartender',
        ]);

        User::create([
            'first_name' => 'Host',
            'last_name' => 'Reception',
            'email' => 'host@restaurant.com',
            'password' => Hash::make('password'),
            'role' => 'host',
        ]);

        // Shifts MUST run before Tables (TableSeeder uses Shift::activeNow())
        $this->call([
            ShiftSeeder::class,
            TableSeeder::class,
            DishSeeder::class,
            ReservationSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
