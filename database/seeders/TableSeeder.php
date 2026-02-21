<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\Shift;
use App\Models\TableAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $waiters = User::where('role', 'waiter')->get();

        if ($waiters->isEmpty()) {
            $waiters = User::factory()->count(3)->create(['role' => 'waiter']);
        }

        $tables = collect();
        for ($i = 1; $i <= 20; $i++) {
            $tables->push(Table::create([
                'table_number' => $i,
                'capacity' => rand(2, 8),
                'status' => 'available',
            ]));
        }

        $activeShifts = Shift::activeNow()->whereIn('user_id', $waiters->pluck('id'))->get();

        if ($activeShifts->isNotEmpty()) {
            foreach ($tables as $table) {
                $shift = $activeShifts->random();
                TableAssignment::create([
                    'table_id' => $table->id,
                    'shift_id' => $shift->id,
                    'user_id' => $shift->user_id,
                ]);
            }
        }
    }
}
