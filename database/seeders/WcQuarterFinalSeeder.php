<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WcQuarterFinalSeeder extends Seeder
{
    public function run(): void
    {
        // Safe to re-run: bail out if the Quarter-finals have already been seeded
        // (these inserts hit the production DB).
        if (DB::table('wc_fixtures')->where('stage', 'quarter_final')->exists()) {
            $this->command?->warn('Quarter-final fixtures already present — skipping.');

            return;
        }

        // Map team code -> teamID for resolving home/away foreign keys.
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        // [home_code, away_code, kick-off (UTC), venue]. No group_letter — knockout.
        $fixtures = [
            ['FRA', 'MAR', '2026-07-09 20:00', 'Gillette Stadium, Foxborough'],
            ['ESP', 'BEL', '2026-07-10 19:00', 'SoFi Stadium, Inglewood'],
            ['NOR', 'ENG', '2026-07-11 21:00', 'Hard Rock Stadium, Miami Gardens'],
            ['ARG', 'SUI', '2026-07-13 01:00', 'Arrowhead Stadium, Kansas City'],
        ];

        $now = now();
        $rows = [];

        foreach ($fixtures as [$home, $away, $kickoff, $venue]) {
            $rows[] = [
                'stage'            => 'quarter_final',
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

        $this->command?->info('Seeded ' . count($rows) . ' Quarter-final fixtures.');
    }
}
