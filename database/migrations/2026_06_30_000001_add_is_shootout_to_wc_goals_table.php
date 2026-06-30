<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wc_goals', function (Blueprint $table) {
            $table->boolean('is_shootout')->default(false)->after('is_own_goal');
        });
    }

    public function down(): void
    {
        Schema::table('wc_goals', function (Blueprint $table) {
            $table->dropColumn('is_shootout');
        });
    }
};
