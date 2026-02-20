<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have some waiters
        $waiters = User::where('role', 'waiter')->get();

        if ($waiters->isEmpty()) {
            $waiters = User::factory()->count(3)->create(['role' => 'waiter']);
        }

        // Create 20 tables
        for ($i = 1; $i <= 20; $i++) {
            Table::create([
                'table_number' => $i,
                'capacity' => rand(2, 8),
                'status' => 'available',
                'waiter_id' => $waiters->random()->id,
            ]);
        }
    }
}
