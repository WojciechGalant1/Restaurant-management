<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Shift;
use App\Models\Table;
use App\Models\TableAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Create rooms
        $mainHall = Room::create([
            'name' => 'Main Hall',
            'description' => 'Primary dining area',
            'color' => '#6366f1',
            'sort_order' => 0,
        ]);

        $terrace = Room::create([
            'name' => 'Terrace',
            'description' => 'Outdoor seating area',
            'color' => '#10b981',
            'sort_order' => 1,
        ]);

        $vipRoom = Room::create([
            'name' => 'VIP Room',
            'description' => 'Private dining room',
            'color' => '#f59e0b',
            'sort_order' => 2,
        ]);

        // Create tables and assign to rooms
        $tables = collect();

        // Main Hall: tables 1-10
        for ($i = 1; $i <= 10; $i++) {
            $tables->push(Table::create([
                'table_number' => $i,
                'capacity' => rand(2, 6),
                'status' => 'available',
                'room_id' => $mainHall->id,
                'sort_order' => $i,
            ]));
        }

        // Terrace: tables 11-16
        for ($i = 11; $i <= 16; $i++) {
            $tables->push(Table::create([
                'table_number' => $i,
                'capacity' => rand(2, 4),
                'status' => 'available',
                'room_id' => $terrace->id,
                'sort_order' => $i - 10,
            ]));
        }

        // VIP Room: tables 17-20
        for ($i = 17; $i <= 20; $i++) {
            $tables->push(Table::create([
                'table_number' => $i,
                'capacity' => rand(4, 8),
                'status' => 'available',
                'room_id' => $vipRoom->id,
                'sort_order' => $i - 16,
            ]));
        }

        // Assign tables to waiters via active shifts
        $waiters = User::where('role', 'waiter')->get();
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
