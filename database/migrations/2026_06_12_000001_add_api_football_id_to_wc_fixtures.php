<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wc_fixtures', function (Blueprint $table) {
            // API-Football fixture id, set once a fixture has been matched.
            // Nullable unique — Postgres allows multiple NULLs in a unique index.
            $table->integer('api_football_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('wc_fixtures', function (Blueprint $table) {
            $table->dropUnique(['api_football_id']);
            $table->dropColumn('api_football_id');
        });
    }
};
