<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('objective');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('audience', 500);
            $table->string('channel', 40);
            $table->text('message');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->decimal('budget_limit', 12, 2)->nullable();
            $table->string('tracking_url', 2048)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('created_by')->constrained('user')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('user')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('marketing_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 30)->default('article');
            $table->longText('body_markdown');
            $table->string('excerpt', 500)->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->foreignId('author_id')->constrained('user')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('marketing_content_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('marketing_contents')->cascadeOnDelete();
            $table->longText('body_markdown');
            $table->foreignId('created_by')->constrained('user')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('marketing_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->unique();
            $table->boolean('enabled')->default(false);
            $table->text('credentials')->nullable();
            $table->json('permissions')->nullable();
            $table->dateTime('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('marketing_events', function (Blueprint $table) {
            $table->id();
            $table->string('event', 60)->index();
            $table->string('session_hash', 64)->nullable()->index();
            $table->string('path', 2048);
            $table->string('referrer_host')->nullable();
            $table->string('utm_source')->nullable()->index();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('marketing_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_audit_logs');
        Schema::dropIfExists('marketing_events');
        Schema::dropIfExists('marketing_integrations');
        Schema::dropIfExists('marketing_content_revisions');
        Schema::dropIfExists('marketing_contents');
        Schema::dropIfExists('marketing_campaigns');
    }
};
