<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game-registrations', function (Blueprint $table) {
            // Explicit bench queue position. 0 = unset, in which case the bench
            // falls back to registrationCreated order (the historical behaviour).
            // Admins set this via the Up/Down reorder controls.
            $table->integer('registrationBenchOrder')->default(0)->after('registrationBench');
        });
    }

    public function down(): void
    {
        Schema::table('game-registrations', function (Blueprint $table) {
            $table->dropColumn('registrationBenchOrder');
        });
    }
};
