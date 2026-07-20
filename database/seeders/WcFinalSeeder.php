<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WcFinalSeeder extends Seeder
{
    public function run(): void
    {
        // Safe to re-run: bail out if the Final has already been seeded
        // (these inserts hit the production DB).
        //
        // NOTE: the guard keys on stage = 'final'. There is no dedicated
        // 'third_place' stage in this app, so if the 3rd-place playoff is ever
        // filed under stage 'final', this guard would treat the Final as
        // already seeded and skip. Seed the Final FIRST, or file the 3rd-place
        // game under a different stage (see the findings report).
        if (DB::table('wc_fixtures')->where('stage', 'final')->exists()) {
            $this->command?->warn('Final fixture already present — skipping.');

            return;
        }

        // Map team code -> teamID for resolving home/away foreign keys.
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        // [home_code, away_code, kick-off (UTC), venue]. No group_letter — knockout.
        // Home/away order mirrors the seeder convention (first = home). Official
        // listing is "Spain v Argentina", so Spain is home.
        $fixtures = [
            ['ESP', 'ARG', '2026-07-19 19:00', 'MetLife Stadium, East Rutherford'],
        ];

        $now = now();
        $rows = [];

        foreach ($fixtures as [$home, $away, $kickoff, $venue]) {
            $rows[] = [
                'stage'            => 'final',
                'group_letter'     => null,
                'home_team_id'     => $teamIds[$home] ?? null,
                'away_team_id'     => $teamIds[$away] ?? null,
                'home_placeholder' => null,
                'away_placeholder' => null,
                'match_datetime'   => Carbon::createFromFormat('Y-m-d H:i', $kickoff, 'UTC'),
                'venue'            => $venue,
                'home_score'       => null,
                'away_score'       => null,
                'status'           => 'scheduled',
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        DB::table('wc_fixtures')->insert($rows);

        $this->command?->info('Seeded ' . count($rows) . ' Final fixture.');
    }
}
