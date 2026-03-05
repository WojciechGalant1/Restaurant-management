<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_clock_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('clocked_in_at');
            $table->timestamps();

            $table->unique(['shift_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_clock_ins');
    }
};
