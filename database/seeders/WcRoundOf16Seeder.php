<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WcRoundOf16Seeder extends Seeder
{
    public function run(): void
    {
        // Safe to re-run: bail out if the Round of 16 has already been seeded
        // (these inserts hit the production DB).
        if (DB::table('wc_fixtures')->where('stage', 'round_of_16')->exists()) {
            $this->command?->warn('Round of 16 fixtures already present — skipping.');

            return;
        }

        // Map team code -> teamID for resolving home/away foreign keys.
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        // [home_code, away_code, kick-off (UTC), venue]. No group_letter — knockout.
        $fixtures = [
            ['CAN', 'MAR', '2026-07-04 17:00', 'NRG Stadium, Houston'],
            ['PAR', 'FRA', '2026-07-04 21:00', 'Lincoln Financial Field, Philadelphia'],
            ['BRA', 'NOR', '2026-07-05 20:00', 'MetLife Stadium, East Rutherford'],
            ['MEX', 'ENG', '2026-07-06 00:00', 'Estadio Azteca, Mexico City'],
            ['ESP', 'POR', '2026-07-06 19:00', 'AT&T Stadium, Arlington'],
            ['USA', 'BEL', '2026-07-07 00:00', 'Lumen Field, Seattle'],
            ['ARG', 'EGY', '2026-07-07 16:00', 'Mercedes-Benz Stadium, Atlanta'],
            ['SUI', 'COL', '2026-07-07 20:00', 'BC Place, Vancouver'],
        ];

        $now = now();
        $rows = [];

        foreach ($fixtures as [$home, $away, $kickoff, $venue]) {
            $rows[] = [
                'stage'            => 'round_of_16',
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

        $this->command?->info('Seeded ' . count($rows) . ' Round of 16 fixtures.');
    }
}
