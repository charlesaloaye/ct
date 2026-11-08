<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ventures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role')->nullable();          // e.g. "Founder & CTO"
            $table->boolean('is_founder')->default(false);
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('years')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->string('status_before_hidden', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('venture_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('logo_path')->nullable()->after('mono');
        });

        Schema::create('appearances', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('outlet');
            $table->string('kind', 20)->default('interview'); // interview | podcast | talk | article | feature
            $table->string('url')->nullable();
            $table->date('published_on')->nullable();
            $table->text('summary')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->string('status_before_hidden', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appearances');
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('venture_id');
            $table->dropColumn('logo_path');
        });
        Schema::dropIfExists('ventures');
    }
};
