<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['table_id', 'shift_id']);
            $table->index(['user_id', 'shift_id']);
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['waiter_id']);
            $table->dropIndex(['waiter_id']);
            $table->dropIndex('tables_waiter_id_status_index');
            $table->dropColumn('waiter_id');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('waiter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('waiter_id');
            $table->index(['waiter_id', 'status']);
        });

        Schema::dropIfExists('table_assignments');
    }
};
