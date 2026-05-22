<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('billing_type', 24)->default('initial')->after('status');
            $table->foreignId('service_id')->nullable()->after('billing_type')->constrained('services')->nullOnDelete();
            $table->timestamp('due_at')->nullable()->after('service_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method_id')->nullable()->after('preference_id');
            $table->string('payment_type_id')->nullable()->after('payment_method_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method_id', 'payment_type_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn(['billing_type', 'due_at']);
        });
    }
};
