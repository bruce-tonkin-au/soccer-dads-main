<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── games: replace gameSeason (VARCHAR seasonKey) with gameSeasonID (INT) ──
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('gameSeasonID')->nullable()->after('gameID');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('UPDATE games SET "gameSeasonID" = s."seasonID" FROM seasons s WHERE games."gameSeason" = s."seasonKey"');
        } else {
            DB::statement('UPDATE games g JOIN seasons s ON g.gameSeason = s.seasonKey SET g.gameSeasonID = s.seasonID');
        }

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('gameSeason');
        });

        // ── results: replace three VARCHAR FK cols and string team col with INTs ──
        Schema::table('results', function (Blueprint $table) {
            $table->unsignedBigInteger('resultGameID')->nullable()->after('resultGame');
            $table->unsignedBigInteger('resultMemberID')->nullable()->after('resultMember');
            $table->unsignedBigInteger('resultSeasonID')->nullable()->after('resultSeason');
            $table->unsignedTinyInteger('resultTeamID')->nullable()->after('resultTeam');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('UPDATE results SET "resultGameID" = g."gameID" FROM games g WHERE results."resultGame" = g."gameKey"');
            DB::statement('UPDATE results SET "resultMemberID" = m."memberID" FROM members m WHERE results."resultMember" = m."memberKey"');
            DB::statement('UPDATE results SET "resultSeasonID" = s."seasonID" FROM seasons s WHERE results."resultSeason" = s."seasonKey"');
        } else {
            DB::statement('UPDATE results r JOIN games g ON r.resultGame = g.gameKey SET r.resultGameID = g.gameID');
            DB::statement('UPDATE results r JOIN members m ON r.resultMember = m.memberKey SET r.resultMemberID = m.memberID');
            DB::statement('UPDATE results r JOIN seasons s ON r.resultSeason = s.seasonKey SET r.resultSeasonID = s.seasonID');
        }

        // Convert hardcoded team string keys to integer team IDs
        DB::table('results')->where('resultTeam', 'DHJ902klu908')->update(['resultTeamID' => 1]);
        DB::table('results')->where('resultTeam', 'WHD891094lkm')->update(['resultTeamID' => 2]);
        DB::table('results')->where('resultTeam', '902ULK982nbg')->update(['resultTeamID' => 3]);

        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn(['resultGame', 'resultMember', 'resultSeason', 'resultTeam']);
        });
    }

    public function down(): void
    {
        // Recreate results VARCHAR columns (data not backfilled — schema rollback only)
        Schema::table('results', function (Blueprint $table) {
            $table->string('resultTeam', 12)->nullable()->after('resultTeamID');
            $table->string('resultSeason', 12)->nullable()->after('resultSeasonID');
            $table->string('resultMember', 12)->nullable()->after('resultMemberID');
            $table->string('resultGame', 12)->nullable()->after('resultGameID');
            $table->dropColumn(['resultGameID', 'resultMemberID', 'resultSeasonID', 'resultTeamID']);
        });

        // Recreate games.gameSeason
        Schema::table('games', function (Blueprint $table) {
            $table->string('gameSeason', 12)->nullable()->after('gameSeasonID');
            $table->dropColumn('gameSeasonID');
        });
    }
};
