<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // MySQL: change column nullability without requiring doctrine/dbal.
        DB::statement('ALTER TABLE `orders` MODIFY `user_id` BIGINT UNSIGNED NULL');

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $firstUserId = DB::table('users')->min('id');

        if ($firstUserId) {
            DB::table('orders')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE `orders` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};

