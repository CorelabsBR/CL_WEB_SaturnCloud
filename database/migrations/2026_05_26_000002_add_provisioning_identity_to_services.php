<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'provision_identifier')) {
                $table->string('provision_identifier')->nullable()->unique()->after('domain');
            }
        });

        Schema::table('pterodactyl_servers', function (Blueprint $table) {
            if (! Schema::hasColumn('pterodactyl_servers', 'external_id')) {
                $table->string('external_id')->nullable()->index()->after('pterodactyl_server_id');
            }

            if (! Schema::hasColumn('pterodactyl_servers', 'provision_identifier')) {
                $table->string('provision_identifier')->nullable()->index()->after('external_id');
            }
        });

        DB::table('services')
            ->whereNull('provision_identifier')
            ->orderBy('id')
            ->get(['id', 'user_id', 'order_id', 'order_item_id'])
            ->each(function ($service) {
                DB::table('services')
                    ->where('id', $service->id)
                    ->update([
                        'provision_identifier' => sprintf(
                            'VTX-SRV-%06d-U%06d-O%06d-I%06d',
                            $service->id,
                            $service->user_id,
                            $service->order_id ?: 0,
                            $service->order_item_id ?: 0
                        ),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('pterodactyl_servers', function (Blueprint $table) {
            if (Schema::hasColumn('pterodactyl_servers', 'provision_identifier')) {
                $table->dropColumn('provision_identifier');
            }

            if (Schema::hasColumn('pterodactyl_servers', 'external_id')) {
                $table->dropColumn('external_id');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'provision_identifier')) {
                $table->dropColumn('provision_identifier');
            }
        });
    }
};
