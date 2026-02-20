<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('shifts')
            ->where('end_time', '00:00:00')
            ->update(['end_time' => '23:59:00']);
    }

    public function down(): void
    {
        DB::table('shifts')
            ->where('end_time', '23:59:00')
            ->update(['end_time' => '00:00:00']);
    }
};
