<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remembers what an item's status was when it was hidden, so "Show" puts it
 * back exactly as it was (a hidden draft stays a draft, a featured project
 * stays featured).
 */
return new class () extends Migration {
    private const TABLES = ['projects', 'products', 'articles', 'services', 'experiences', 'testimonials'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('status_before_hidden', 20)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('status_before_hidden'));
        }
    }
};
