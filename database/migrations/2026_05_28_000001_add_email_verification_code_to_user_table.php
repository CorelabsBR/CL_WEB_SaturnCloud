<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user', 'email_verification_code')) {
            Schema::table('user', function (Blueprint $table) {
                $table->string('email_verification_code', 6)->nullable()->after('email_verified_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user', 'email_verification_code')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropColumn('email_verification_code');
            });
        }
    }
};
