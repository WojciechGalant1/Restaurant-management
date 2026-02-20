<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $waiters = User::where('role', 'waiter')->get();

        if ($waiters->isEmpty()) {
            return;
        }

        // Create shifts for the last 30 days for each waiter
        $waiters->each(function (User $waiter) {
            Shift::factory()
                ->count(10)
                ->for($waiter)
                ->create();
        });
    }
}
