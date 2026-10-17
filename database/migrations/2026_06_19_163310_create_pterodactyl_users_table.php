<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pterodactyl_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('user')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('pterodactyl_user_id')
                ->unique();

            $table->string('email')
                ->unique();

            $table->string('username')
                ->nullable();

            $table->text('encrypted_password')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pterodactyl_users');
    }
};