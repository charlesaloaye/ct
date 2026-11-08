<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Articles Charles published on other platforms (Medium, DEV, Hashnode...), linked from the blog.
        Schema::create('writings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('publication');
            $table->string('url')->unique();
            $table->date('published_on')->nullable();
            $table->text('summary')->nullable();
            $table->string('status', 20)->default('published')->index();
            $table->string('status_before_hidden', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writings');
    }
};
