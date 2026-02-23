<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE reservations 
            MODIFY COLUMN status 
            ENUM('pending', 'confirmed', 'walk_in_seated', 'seated', 'completed', 'cancelled', 'no_show') 
            NOT NULL 
            DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE reservations 
            MODIFY COLUMN status 
            ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') 
            NOT NULL 
            DEFAULT 'pending'
        ");
    }
};
