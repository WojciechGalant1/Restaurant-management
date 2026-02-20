<?php

namespace Database\Seeders;

use App\Enums\ShiftType;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::whereIn('role', ['waiter', 'chef', 'bartender'])->get();

        if ($staff->isEmpty()) {
            return;
        }

        $shiftTypes = [ShiftType::Morning, ShiftType::Evening];

        // Create shifts for the last 14 days, avoiding duplicates
        $staff->each(function (User $user) use ($shiftTypes) {
            for ($i = 0; $i < 14; $i++) {
                $date = now()->subDays($i)->toDateString();
                $type = $shiftTypes[array_rand($shiftTypes)];

                Shift::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date,
                        'shift_type' => $type->value,
                    ],
                    [
                        'start_time' => $type->startTime(),
                        'end_time' => $type->endTime(),
                        'notes' => null,
                    ]
                );
            }
        });
    }
}
