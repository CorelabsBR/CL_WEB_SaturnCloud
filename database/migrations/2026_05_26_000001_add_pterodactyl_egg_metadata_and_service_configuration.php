<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pterodactyl_eggs', function (Blueprint $table) {
            if (! Schema::hasColumn('pterodactyl_eggs', 'nest_id')) {
                $table->unsignedInteger('nest_id')->nullable()->after('egg_id');
            }

            if (! Schema::hasColumn('pterodactyl_eggs', 'startup')) {
                $table->text('startup')->nullable()->after('name');
            }

            if (! Schema::hasColumn('pterodactyl_eggs', 'docker_image')) {
                $table->string('docker_image')->nullable()->after('startup');
            }

            if (! Schema::hasColumn('pterodactyl_eggs', 'docker_images')) {
                $table->json('docker_images')->nullable()->after('docker_image');
            }

            if (! Schema::hasColumn('pterodactyl_eggs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });

        if (! Schema::hasTable('pterodactyl_egg_variables')) {
            Schema::create('pterodactyl_egg_variables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pterodactyl_egg_id')
                    ->constrained('pterodactyl_eggs')
                    ->cascadeOnDelete();
                $table->unsignedInteger('pterodactyl_variable_id')->nullable();
                $table->string('name')->nullable();
                $table->string('env_variable');
                $table->text('default_value')->nullable();
                $table->text('rules')->nullable();
                $table->boolean('required')->default(false);
                $table->boolean('user_viewable')->default(false);
                $table->boolean('user_editable')->default(false);
                $table->timestamps();

                $table->unique(['pterodactyl_egg_id', 'env_variable'], 'uq_ptero_egg_env_variable');
            });
        }

        if (! Schema::hasTable('service_provisioning_configurations')) {
            Schema::create('service_provisioning_configurations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')
                    ->constrained('services')
                    ->cascadeOnDelete();
                $table->json('environment')->nullable();
                $table->json('last_payload')->nullable();
                $table->json('last_error')->nullable();
                $table->timestamps();

                $table->unique('service_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_provisioning_configurations');
        Schema::dropIfExists('pterodactyl_egg_variables');

        Schema::table('pterodactyl_eggs', function (Blueprint $table) {
            foreach (['updated_at', 'docker_images', 'docker_image', 'startup', 'nest_id'] as $column) {
                if (Schema::hasColumn('pterodactyl_eggs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
