<?php

use App\Enums\ShiftType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('shift_type');
            $table->time('end_time')->nullable()->after('start_time');
        });

        // Populate existing rows from ShiftType enum defaults
        foreach (ShiftType::cases() as $type) {
            DB::table('shifts')
                ->where('shift_type', $type->value)
                ->update([
                    'start_time' => $type->startTime(),
                    'end_time' => $type->endTime(),
                ]);
        }

        // Make columns non-nullable after populating
        Schema::table('shifts', function (Blueprint $table) {
            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
