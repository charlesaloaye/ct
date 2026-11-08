<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained('article_categories')->nullOnDelete();
            $table->text('excerpt')->nullable();
            $table->json('body')->nullable();
            $table->json('tags')->nullable();
            $table->string('cover_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedSmallInteger('read_minutes')->default(1);
            $table->timestamp('published_at')->nullable()->index();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->boolean('notify_subscribers')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
