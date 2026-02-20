<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $tables = Table::all();

        if ($tables->isEmpty()) {
            return;
        }

        // Create 50 reservations
        Reservation::factory()
            ->count(50)
            ->recycle($tables)
            ->create();
    }
}
