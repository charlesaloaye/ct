<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tag')->nullable();
            $table->text('description')->nullable();
            $table->string('domain')->nullable();
            $table->string('url')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('show_on_mobile')->default(true);
            $table->boolean('has_case_study')->default(false);
            $table->string('cs_category')->nullable();
            $table->string('cs_title')->nullable();
            $table->text('cs_summary')->nullable();
            $table->string('client')->nullable();
            $table->string('role')->nullable();
            $table->string('timeline')->nullable();
            $table->string('since')->nullable();
            $table->text('challenge')->nullable();
            $table->text('built')->nullable();
            $table->text('outcome')->nullable();
            $table->json('features')->nullable();
            $table->json('results')->nullable();
            $table->json('gallery')->nullable();
            $table->json('tech_stack')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
