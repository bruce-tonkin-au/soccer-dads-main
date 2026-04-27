<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game-registrations', function (Blueprint $table) {
            $table->tinyInteger('registrationBench')->default(0)->after('registrationStatus');
        });
    }

    public function down(): void
    {
        Schema::table('game-registrations', function (Blueprint $table) {
            $table->dropColumn('registrationBench');
        });
    }
};
