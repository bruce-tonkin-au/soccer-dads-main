<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // memberKey was the string-based surrogate key for members.
        // It was replaced by memberID (auto-increment) as the FK
        // in all results/joins. No application code reads or writes
        // this column after migration 2026_04_28_000001.
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('memberKey');
        });

        // gameKey was the string-based surrogate key for games.
        // Replaced by gameID in results.resultGameID and all joins.
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('gameKey');
        });

        // seasonKey was the string-based surrogate key for seasons.
        // Replaced by seasonID in games.gameSeasonID and all joins.
        // seasonLink (user-facing slug) is separate and stays.
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('seasonKey');
        });
    }

    public function down(): void
    {
        // Restore columns as nullable — existing rows will have NULL.
        // Original 12-char values are permanently gone.
        Schema::table('members', function (Blueprint $table) {
            $table->string('memberKey', 12)->nullable()->after('memberID');
        });

        Schema::table('games', function (Blueprint $table) {
            $table->string('gameKey', 12)->nullable()->after('gameID');
        });

        Schema::table('seasons', function (Blueprint $table) {
            $table->string('seasonKey', 12)->nullable()->after('seasonID');
        });
    }
};
