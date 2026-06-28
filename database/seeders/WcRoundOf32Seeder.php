<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WcRoundOf32Seeder extends Seeder
{
    public function run(): void
    {
        // Safe to re-run: bail out if the Round of 32 has already been seeded
        // (these inserts hit the production DB).
        if (DB::table('wc_fixtures')->where('stage', 'round_of_32')->exists()) {
            $this->command?->warn('Round of 32 fixtures already present — skipping.');

            return;
        }

        // Map team code -> teamID for resolving home/away foreign keys.
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        // [home_code, away_code, kick-off (UTC), venue]. No group_letter — knockout.
        $fixtures = [
            ['RSA', 'CAN', '2026-06-28 19:00', 'SoFi Stadium, Inglewood'],
            ['BRA', 'JPN', '2026-06-29 17:00', 'NRG Stadium, Houston'],
            ['GER', 'PAR', '2026-06-29 20:30', 'Gillette Stadium, Foxborough'],
            ['NED', 'MAR', '2026-06-30 01:00', 'Estadio BBVA, Guadalupe'],
            ['CIV', 'NOR', '2026-06-30 17:00', 'AT&T Stadium, Arlington'],
            ['FRA', 'SWE', '2026-06-30 21:00', 'MetLife Stadium, East Rutherford'],
            ['MEX', 'ECU', '2026-07-01 01:00', 'Estadio Azteca, Mexico City'],
            ['ENG', 'COD', '2026-07-01 16:00', 'Mercedes-Benz Stadium, Atlanta'],
            ['BEL', 'SEN', '2026-07-01 20:00', 'Lumen Field, Seattle'],
            ['USA', 'BIH', '2026-07-02 00:00', 'Levi\'s Stadium, Santa Clara'],
            ['AUS', 'EGY', '2026-07-02 18:00', 'AT&T Stadium, Arlington'],
            ['ARG', 'CPV', '2026-07-02 22:00', 'Hard Rock Stadium, Miami Gardens'],
            ['COL', 'GHA', '2026-07-03 01:30', 'Arrowhead Stadium, Kansas City'],
            ['ESP', 'AUT', '2026-07-03 19:00', 'SoFi Stadium, Inglewood'],
            ['POR', 'CRO', '2026-07-03 23:00', 'BMO Field, Toronto'],
            ['SUI', 'ALG', '2026-07-04 01:00', 'Estadio BBVA, Guadalupe'],
        ];

        $now = now();
        $rows = [];

        foreach ($fixtures as [$home, $away, $kickoff, $venue]) {
            $rows[] = [
                'stage'            => 'round_of_32',
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

        $this->command?->info('Seeded ' . count($rows) . ' Round of 32 fixtures.');
    }
}
