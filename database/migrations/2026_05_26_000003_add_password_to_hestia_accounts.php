<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hestia_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('hestia_accounts', 'password')) {
                $table->text('password')->nullable()->after('hestia_user');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hestia_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('hestia_accounts', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
};
