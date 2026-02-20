<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->integer('table_number')->unique();
            $table->integer('capacity');
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->foreignId('waiter_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('waiter_id');
            $table->index('status');
            $table->index(['waiter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
