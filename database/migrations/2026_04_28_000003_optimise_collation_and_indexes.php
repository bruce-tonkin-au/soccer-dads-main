<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ── Helper: skip silently if an index already exists ────────────────
    private function addIndex(string $table, callable $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (\Illuminate\Database\QueryException $e) {
            // Error 1061: duplicate key name — index already exists, skip.
            if (!str_contains($e->getMessage(), '1061')) {
                throw $e;
            }
        }
    }

    // ════════════════════════════════════════════════════════════════════
    // UP
    // ════════════════════════════════════════════════════════════════════
    public function up(): void
    {
        // ── PART 1: Collation ────────────────────────────────────────────
        // Converts every VARCHAR/TEXT/CHAR column in each table to
        // utf8mb4_unicode_ci. The database-level default is also updated
        // so all future tables inherit the correct charset.
        //
        // Skips tables already created by Laravel migrations with the
        // correct config (member_tokens, player-ratings, messages, users)
        // since CONVERT TO is idempotent for those.

        $db = DB::connection()->getDatabaseName();
        DB::statement("ALTER DATABASE `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $legacyTables = [
            'members',
            'seasons',
            'games',
            'game-registrations',
            'scoring',
            'scoring-actions',
            'results',
            'account',
            'account-payments',
            'season-awards',
            // Laravel-created but included for completeness:
            'member_tokens',
            'player-ratings',
            'messages',
            'users',
        ];

        foreach ($legacyTables as $table) {
            DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        // ── PART 2: Indexes ──────────────────────────────────────────────
        // Add missing indexes on FK columns and columns appearing in
        // WHERE / JOIN / GROUP BY clauses across the application.
        // Uses addIndex() helper to skip silently if already present.

        // ── members ─────────────────────────────────────────────────────
        // memberCode: lookup key in /reg, /rate, /topup, /msg routes
        // memberSlug: lookup key in /players/{slug} routes
        // memberActive: filtered on almost every member query
        // memberParent: WHERE clause in portal (child lookup)
        $this->addIndex('members', fn (Blueprint $t) =>
            $t->unique('memberCode', 'members_memberCode_unique'));

        $this->addIndex('members', fn (Blueprint $t) =>
            $t->unique('memberSlug', 'members_memberSlug_unique'));

        $this->addIndex('members', fn (Blueprint $t) =>
            $t->index('memberActive', 'members_memberActive_index'));

        $this->addIndex('members', fn (Blueprint $t) =>
            $t->index('memberParent', 'members_memberParent_index'));

        // ── seasons ─────────────────────────────────────────────────────
        // seasonLink: lookup key in /seasons/{link} public routes
        // seasonVisible: filtered on every season list/detail query
        $this->addIndex('seasons', fn (Blueprint $t) =>
            $t->unique('seasonLink', 'seasons_seasonLink_unique'));

        $this->addIndex('seasons', fn (Blueprint $t) =>
            $t->index('seasonVisible', 'seasons_seasonVisible_index'));

        // ── games ────────────────────────────────────────────────────────
        // gameSeasonID: new FK column (no index created by migration 000001)
        // gameVisible: filtered on every game list query
        // (gameSeasonID, gameVisible): composite covers the common
        //   WHERE gameSeasonID = ? AND gameVisible = 1 pattern
        $this->addIndex('games', fn (Blueprint $t) =>
            $t->index('gameSeasonID', 'games_gameSeasonID_index'));

        $this->addIndex('games', fn (Blueprint $t) =>
            $t->index('gameVisible', 'games_gameVisible_index'));

        $this->addIndex('games', fn (Blueprint $t) =>
            $t->index(['gameSeasonID', 'gameVisible'], 'games_season_visible_index'));

        // ── game-registrations ───────────────────────────────────────────
        // gameID + memberID: both FK columns, always queried together
        // registrationStatus: filtered on every registration count query
        // (gameID, registrationStatus): composite covers capacity checks
        //   and registered-player lists
        $this->addIndex('game-registrations', fn (Blueprint $t) =>
            $t->index('gameID', 'gamereg_gameID_index'));

        $this->addIndex('game-registrations', fn (Blueprint $t) =>
            $t->index('memberID', 'gamereg_memberID_index'));

        $this->addIndex('game-registrations', fn (Blueprint $t) =>
            $t->index('registrationStatus', 'gamereg_status_index'));

        $this->addIndex('game-registrations', fn (Blueprint $t) =>
            $t->index(['gameID', 'registrationStatus'], 'gamereg_game_status_index'));

        $this->addIndex('game-registrations', fn (Blueprint $t) =>
            $t->unique(['gameID', 'memberID'], 'gamereg_game_member_unique'));

        // ── scoring ──────────────────────────────────────────────────────
        // gameID: FK, joined in almost every scoring query
        // scoringActive: always filtered
        // scoringEnded: whereNotNull in 4 controllers to detect finished games
        $this->addIndex('scoring', fn (Blueprint $t) =>
            $t->index('gameID', 'scoring_gameID_index'));

        $this->addIndex('scoring', fn (Blueprint $t) =>
            $t->index('scoringActive', 'scoring_active_index'));

        $this->addIndex('scoring', fn (Blueprint $t) =>
            $t->index('scoringEnded', 'scoring_ended_index'));

        // ── scoring-actions ──────────────────────────────────────────────
        // scoringID: FK join to scoring table
        // memberID: FK + WHERE + GROUP BY (goal counts per player)
        // secondID: FK-like + WHERE + GROUP BY (assist counts)
        // teamID: WHERE + GROUP BY (goals by team)
        // typeID: WHERE (save type filter)
        // actionGoal + actionActive: always both present — composite
        // memberID composite: covers the very common goals-per-player query
        // gameID: direct FK used in PlayersController joins
        $this->addIndex('scoring-actions', fn (Blueprint $t) =>
            $t->index('scoringID', 'scoringact_scoringID_index'));

        $this->addIndex('scoring-actions', fn (Blueprint $t) =>
            $t->index('memberID', 'scoringact_memberID_index'));

        $this->addIndex('scoring-actions', fn (Blueprint $t) =>
            $t->index('secondID', 'scoringact_secondID_index'));

        $this->addIndex('scoring-actions', fn (Blueprint $t) =>
            $t->index('teamID', 'scoringact_teamID_index'));

        $this->addIndex('scoring-actions', fn (Blueprint $t) =>
            $t->index('typeID', 'scoringact_typeID_index'));

        $this->addIndex('scoring-actions', fn (Blueprint $t) =>
            $t->index('gameID', 'scoringact_gameID_index'));

        $this->addIndex('scoring-actions', fn (Blueprint $t) =>
            $t->index(['actionGoal', 'actionActive'], 'scoringact_goal_active_index'));

        $this->addIndex('scoring-actions', fn (Blueprint $t) =>
            $t->index(['memberID', 'actionGoal', 'actionActive'], 'scoringact_member_goal_active_index'));

        // ── results ──────────────────────────────────────────────────────
        // All three FK columns added by migration 000001 — no indexes yet
        // resultActive: WHERE on nearly every results query
        // Composites: the two most common query patterns
        $this->addIndex('results', fn (Blueprint $t) =>
            $t->index('resultGameID', 'results_gameID_index'));

        $this->addIndex('results', fn (Blueprint $t) =>
            $t->index('resultMemberID', 'results_memberID_index'));

        $this->addIndex('results', fn (Blueprint $t) =>
            $t->index('resultSeasonID', 'results_seasonID_index'));

        $this->addIndex('results', fn (Blueprint $t) =>
            $t->index('resultActive', 'results_active_index'));

        $this->addIndex('results', fn (Blueprint $t) =>
            $t->index(['resultMemberID', 'resultActive'], 'results_member_active_index'));

        $this->addIndex('results', fn (Blueprint $t) =>
            $t->index(['resultGameID', 'resultActive'], 'results_game_active_index'));

        // ── account ──────────────────────────────────────────────────────
        // memberID: FK + WHERE on every balance/transaction query
        // accountVisible: always filtered
        // gameID: leftJoin in portal account history
        // (memberID, accountVisible): covers the most common balance query
        $this->addIndex('account', fn (Blueprint $t) =>
            $t->index('memberID', 'account_memberID_index'));

        $this->addIndex('account', fn (Blueprint $t) =>
            $t->index('accountVisible', 'account_visible_index'));

        $this->addIndex('account', fn (Blueprint $t) =>
            $t->index('gameID', 'account_gameID_index'));

        $this->addIndex('account', fn (Blueprint $t) =>
            $t->index(['memberID', 'accountVisible'], 'account_member_visible_index'));

        // ── account-payments ─────────────────────────────────────────────
        // memberID: FK
        // formPaymentID: WHERE in fulfillPayment() — Stripe session lookup,
        //   must be unique (one session → one payment record)
        $this->addIndex('account-payments', fn (Blueprint $t) =>
            $t->index('memberID', 'acctpay_memberID_index'));

        $this->addIndex('account-payments', fn (Blueprint $t) =>
            $t->unique('formPaymentID', 'acctpay_formPaymentID_unique'));

        // ── season-awards ────────────────────────────────────────────────
        // seasonID: FK + WHERE
        // awardActive: WHERE
        // awardPlayer1/2/3: OR WHERE conditions in award history queries
        $this->addIndex('season-awards', fn (Blueprint $t) =>
            $t->index('seasonID', 'awards_seasonID_index'));

        $this->addIndex('season-awards', fn (Blueprint $t) =>
            $t->index('awardActive', 'awards_active_index'));

        $this->addIndex('season-awards', fn (Blueprint $t) =>
            $t->index(['awardPlayer1', 'awardPlayer2', 'awardPlayer3'], 'awards_players_index'));

        // ── member_tokens ─────────────────────────────────────────────────
        // token: already UNIQUE from create migration — skip
        // memberID: not indexed in create migration
        $this->addIndex('member_tokens', fn (Blueprint $t) =>
            $t->index('memberID', 'tokens_memberID_index'));

        // ── player-ratings ────────────────────────────────────────────────
        // (raterMemberID, ratedMemberID): already UNIQUE from create migration
        // ratedMemberID alone: GROUP BY in ratings summary query
        $this->addIndex('player-ratings', fn (Blueprint $t) =>
            $t->index('ratedMemberID', 'ratings_ratedMemberID_index'));

        // ── PART 3: Drop vestigial columns ───────────────────────────────
        // resultVisited: written as 0 on every insert, never read anywhere
        // in controllers, views, or queries. Safe to drop.
        //
        // resultPoints is intentionally NOT dropped here — scheduled for
        // a future cleanup pass.
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn('resultVisited');
        });
    }

    // ════════════════════════════════════════════════════════════════════
    // DOWN
    // ════════════════════════════════════════════════════════════════════
    public function down(): void
    {
        // ── PART 3 rollback: restore resultVisited ────────────────────────
        Schema::table('results', function (Blueprint $table) {
            $table->tinyInteger('resultVisited')->default(0)->after('resultActive');
        });

        // ── Collation rollback ────────────────────────────────────────────
        // WARNING: converting back to utf8mb3 will silently truncate or
        // corrupt any 4-byte characters (emoji, some CJK) stored since
        // the up() migration ran. Only proceed if you are certain no such
        // data was written.
        $db = DB::connection()->getDatabaseName();
        DB::statement("ALTER DATABASE `{$db}` CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci");

        foreach ([
            'members', 'seasons', 'games', 'game-registrations',
            'scoring', 'scoring-actions', 'results', 'account',
            'account-payments', 'season-awards',
        ] as $table) {
            DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci");
        }

        // ── Index rollback ────────────────────────────────────────────────
        Schema::table('members', function (Blueprint $t) {
            $t->dropUnique('members_memberCode_unique');
            $t->dropUnique('members_memberSlug_unique');
            $t->dropIndex('members_memberActive_index');
            $t->dropIndex('members_memberParent_index');
        });
        Schema::table('seasons', function (Blueprint $t) {
            $t->dropUnique('seasons_seasonLink_unique');
            $t->dropIndex('seasons_seasonVisible_index');
        });
        Schema::table('games', function (Blueprint $t) {
            $t->dropIndex('games_gameSeasonID_index');
            $t->dropIndex('games_gameVisible_index');
            $t->dropIndex('games_season_visible_index');
        });
        Schema::table('game-registrations', function (Blueprint $t) {
            $t->dropIndex('gamereg_gameID_index');
            $t->dropIndex('gamereg_memberID_index');
            $t->dropIndex('gamereg_status_index');
            $t->dropIndex('gamereg_game_status_index');
            $t->dropUnique('gamereg_game_member_unique');
        });
        Schema::table('scoring', function (Blueprint $t) {
            $t->dropIndex('scoring_gameID_index');
            $t->dropIndex('scoring_active_index');
            $t->dropIndex('scoring_ended_index');
        });
        Schema::table('scoring-actions', function (Blueprint $t) {
            $t->dropIndex('scoringact_scoringID_index');
            $t->dropIndex('scoringact_memberID_index');
            $t->dropIndex('scoringact_secondID_index');
            $t->dropIndex('scoringact_teamID_index');
            $t->dropIndex('scoringact_typeID_index');
            $t->dropIndex('scoringact_gameID_index');
            $t->dropIndex('scoringact_goal_active_index');
            $t->dropIndex('scoringact_member_goal_active_index');
        });
        Schema::table('results', function (Blueprint $t) {
            $t->dropIndex('results_gameID_index');
            $t->dropIndex('results_memberID_index');
            $t->dropIndex('results_seasonID_index');
            $t->dropIndex('results_active_index');
            $t->dropIndex('results_member_active_index');
            $t->dropIndex('results_game_active_index');
        });
        Schema::table('account', function (Blueprint $t) {
            $t->dropIndex('account_memberID_index');
            $t->dropIndex('account_visible_index');
            $t->dropIndex('account_gameID_index');
            $t->dropIndex('account_member_visible_index');
        });
        Schema::table('account-payments', function (Blueprint $t) {
            $t->dropIndex('acctpay_memberID_index');
            $t->dropUnique('acctpay_formPaymentID_unique');
        });
        Schema::table('season-awards', function (Blueprint $t) {
            $t->dropIndex('awards_seasonID_index');
            $t->dropIndex('awards_active_index');
            $t->dropIndex('awards_players_index');
        });
        Schema::table('member_tokens', function (Blueprint $t) {
            $t->dropIndex('tokens_memberID_index');
        });
        Schema::table('player-ratings', function (Blueprint $t) {
            $t->dropIndex('ratings_ratedMemberID_index');
        });
    }
};
