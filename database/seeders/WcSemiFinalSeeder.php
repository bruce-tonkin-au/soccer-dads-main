<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WcSemiFinalSeeder extends Seeder
{
    public function run(): void
    {
        // Safe to re-run: bail out if the Semi-finals have already been seeded
        // (these inserts hit the production DB).
        if (DB::table('wc_fixtures')->where('stage', 'semi_final')->exists()) {
            $this->command?->warn('Semi-final fixtures already present — skipping.');

            return;
        }

        // Map team code -> teamID for resolving home/away foreign keys.
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        // [home_code, away_code, kick-off (UTC), venue]. No group_letter — knockout.
        $fixtures = [
            ['FRA', 'ESP', '2026-07-14 19:00', 'AT&T Stadium, Arlington'],
            ['ENG', 'ARG', '2026-07-15 19:00', 'Mercedes-Benz Stadium, Atlanta'],
        ];

        $now = now();
        $rows = [];

        foreach ($fixtures as [$home, $away, $kickoff, $venue]) {
            $rows[] = [
                'stage'            => 'semi_final',
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

        $this->command?->info('Seeded ' . count($rows) . ' Semi-final fixtures.');
    }
}
