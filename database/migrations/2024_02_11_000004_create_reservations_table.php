<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('phone_number');
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->integer('party_size');
            $table->enum('status', ['pending', 'confirmed', 'walk_in_seated', 'seated', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->integer('duration_minutes')->default(120);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
