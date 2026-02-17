<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('waiter_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('waiter_id');
            $table->index('status');
            $table->index(['waiter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['waiter_id']);
            $table->dropIndex(['waiter_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['waiter_id', 'status']);
            $table->dropColumn('waiter_id');
        });
    }
};

