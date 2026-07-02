<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ties a season to a night. Nullable on purpose: legacy/historical seasons
     * never get a night and stay null (harmless — resolvers filter seasonVisible=1).
     * Integer to match seasons.seasonID / nights.nightID. Does not touch the dead
     * seasonLocation column.
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->integer('nightID')->nullable()->after('seasonLocation');

            $table->foreign('nightID')
                ->references('nightID')->on('nights')
                ->nullOnDelete();

            $table->index('nightID');
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropForeign(['nightID']);
            $table->dropIndex(['nightID']);
            $table->dropColumn('nightID');
        });
    }
};
