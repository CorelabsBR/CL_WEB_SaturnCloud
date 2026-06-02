<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->timestamp('timeout_until')->nullable()->after('profile_completed_at');
            $table->string('timeout_reason')->nullable()->after('timeout_until');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['timeout_until', 'timeout_reason']);
        });
    }
};
