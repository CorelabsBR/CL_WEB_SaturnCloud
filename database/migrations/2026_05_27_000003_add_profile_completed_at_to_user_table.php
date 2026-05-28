<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user', 'profile_completed_at')) {
            Schema::table('user', function (Blueprint $table) {
                $table->timestamp('profile_completed_at')->nullable()->after('email_verified_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user', 'profile_completed_at')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropColumn('profile_completed_at');
            });
        }
    }
};
