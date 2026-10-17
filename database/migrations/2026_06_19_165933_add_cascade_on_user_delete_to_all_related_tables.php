<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('user')
                ->cascadeOnDelete();
        });

        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('user')
                ->cascadeOnDelete();
        });

        // These tables previously had only an index. Remove possible orphaned
        // records before enforcing the relationship at the database level.
        DB::table('checkout_sessions')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('user')
                    ->whereColumn('user.id', 'checkout_sessions.user_id');
            })
            ->delete();

        DB::table('sessions')
            ->whereNotNull('user_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('user')
                    ->whereColumn('user.id', 'sessions.user_id');
            })
            ->delete();

        Schema::table('checkout_sessions', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('user')
                ->cascadeOnDelete();
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('user')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('checkout_sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('user')
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('user');
        });
    }
};
