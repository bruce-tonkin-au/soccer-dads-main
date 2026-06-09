<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WcFixtureSeeder extends Seeder
{
    public function run(): void
    {
        // Map team code -> teamID for resolving home/away foreign keys.
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        // [group_letter, home_code, away_code, kick-off (UTC), venue]
        $fixtures = [
            // GROUP A
            ['A', 'MEX', 'RSA', '2026-06-11 19:00', 'Estadio Azteca, Mexico City'],
            ['A', 'KOR', 'CZE', '2026-06-12 02:00', 'Estadio Akron, Zapopan'],
            ['A', 'CZE', 'RSA', '2026-06-18 16:00', 'Mercedes-Benz Stadium, Atlanta'],
            ['A', 'MEX', 'KOR', '2026-06-19 01:00', 'Estadio Akron, Zapopan'],
            ['A', 'CZE', 'MEX', '2026-06-25 01:00', 'Estadio Azteca, Mexico City'],
            ['A', 'RSA', 'KOR', '2026-06-25 01:00', 'Estadio BBVA, Guadalupe'],

            // GROUP B
            ['B', 'CAN', 'BIH', '2026-06-12 19:00', 'BMO Field, Toronto'],
            ['B', 'QAT', 'SUI', '2026-06-13 19:00', 'Levi\'s Stadium, Santa Clara'],
            ['B', 'SUI', 'BIH', '2026-06-18 19:00', 'SoFi Stadium, Inglewood'],
            ['B', 'CAN', 'QAT', '2026-06-18 22:00', 'BC Place, Vancouver'],
            ['B', 'SUI', 'CAN', '2026-06-24 19:00', 'BC Place, Vancouver'],
            ['B', 'BIH', 'QAT', '2026-06-24 19:00', 'Lumen Field, Seattle'],

            // GROUP C
            ['C', 'BRA', 'MAR', '2026-06-13 22:00', 'MetLife Stadium, East Rutherford'],
            ['C', 'HAI', 'SCO', '2026-06-14 01:00', 'Gillette Stadium, Foxborough'],
            ['C', 'SCO', 'MAR', '2026-06-19 22:00', 'Gillette Stadium, Foxborough'],
            ['C', 'BRA', 'HAI', '2026-06-20 01:00', 'Lincoln Financial Field, Philadelphia'],
            ['C', 'SCO', 'BRA', '2026-06-24 22:00', 'Hard Rock Stadium, Miami Gardens'],
            ['C', 'MAR', 'HAI', '2026-06-24 22:00', 'Mercedes-Benz Stadium, Atlanta'],

            // GROUP D
            ['D', 'USA', 'PAR', '2026-06-13 01:00', 'SoFi Stadium, Inglewood'],
            ['D', 'AUS', 'TUR', '2026-06-13 04:00', 'BC Place, Vancouver'],
            ['D', 'TUR', 'PAR', '2026-06-19 04:00', 'Levi\'s Stadium, Santa Clara'],
            ['D', 'USA', 'AUS', '2026-06-19 19:00', 'Lumen Field, Seattle'],
            ['D', 'TUR', 'USA', '2026-06-26 02:00', 'SoFi Stadium, Inglewood'],
            ['D', 'PAR', 'AUS', '2026-06-26 02:00', 'Levi\'s Stadium, Santa Clara'],

            // GROUP E
            ['E', 'GER', 'CUW', '2026-06-14 17:00', 'NRG Stadium, Houston'],
            ['E', 'CIV', 'ECU', '2026-06-14 23:00', 'Lincoln Financial Field, Philadelphia'],
            ['E', 'GER', 'CIV', '2026-06-20 20:00', 'BMO Field, Toronto'],
            ['E', 'ECU', 'CUW', '2026-06-21 00:00', 'Arrowhead Stadium, Kansas City'],
            ['E', 'ECU', 'GER', '2026-06-25 20:00', 'MetLife Stadium, East Rutherford'],
            ['E', 'CUW', 'CIV', '2026-06-25 20:00', 'Lincoln Financial Field, Philadelphia'],

            // GROUP F
            ['F', 'NED', 'JPN', '2026-06-14 20:00', 'AT&T Stadium, Arlington'],
            ['F', 'SWE', 'TUN', '2026-06-15 02:00', 'Estadio BBVA, Guadalupe'],
            ['F', 'NED', 'SWE', '2026-06-20 17:00', 'NRG Stadium, Houston'],
            ['F', 'TUN', 'JPN', '2026-06-20 04:00', 'Estadio BBVA, Guadalupe'],
            ['F', 'TUN', 'NED', '2026-06-25 23:00', 'AT&T Stadium, Arlington'],
            ['F', 'JPN', 'SWE', '2026-06-25 23:00', 'Arrowhead Stadium, Kansas City'],

            // GROUP G
            ['G', 'BEL', 'EGY', '2026-06-15 19:00', 'Lumen Field, Seattle'],
            ['G', 'IRN', 'NZL', '2026-06-16 01:00', 'SoFi Stadium, Inglewood'],
            ['G', 'BEL', 'IRN', '2026-06-21 19:00', 'SoFi Stadium, Inglewood'],
            ['G', 'NZL', 'EGY', '2026-06-22 01:00', 'BC Place, Vancouver'],
            ['G', 'NZL', 'BEL', '2026-06-27 03:00', 'BC Place, Vancouver'],
            ['G', 'EGY', 'IRN', '2026-06-27 03:00', 'Lumen Field, Seattle'],

            // GROUP H
            ['H', 'ESP', 'CPV', '2026-06-15 16:00', 'Mercedes-Benz Stadium, Atlanta'],
            ['H', 'KSA', 'URU', '2026-06-15 22:00', 'Hard Rock Stadium, Miami Gardens'],
            ['H', 'ESP', 'KSA', '2026-06-21 16:00', 'Mercedes-Benz Stadium, Atlanta'],
            ['H', 'URU', 'CPV', '2026-06-21 22:00', 'Hard Rock Stadium, Miami Gardens'],
            ['H', 'URU', 'ESP', '2026-06-27 00:00', 'NRG Stadium, Houston'],
            ['H', 'CPV', 'KSA', '2026-06-27 00:00', 'Estadio Akron, Zapopan'],

            // GROUP I
            ['I', 'FRA', 'SEN', '2026-06-16 19:00', 'MetLife Stadium, East Rutherford'],
            ['I', 'IRQ', 'NOR', '2026-06-16 22:00', 'Gillette Stadium, Foxborough'],
            ['I', 'FRA', 'IRQ', '2026-06-22 21:00', 'Lincoln Financial Field, Philadelphia'],
            ['I', 'NOR', 'SEN', '2026-06-23 00:00', 'MetLife Stadium, East Rutherford'],
            ['I', 'NOR', 'FRA', '2026-06-26 19:00', 'Gillette Stadium, Foxborough'],
            ['I', 'SEN', 'IRQ', '2026-06-26 19:00', 'BMO Field, Toronto'],

            // GROUP J
            ['J', 'ARG', 'ALG', '2026-06-17 01:00', 'Arrowhead Stadium, Kansas City'],
            ['J', 'AUT', 'JOR', '2026-06-17 04:00', 'Levi\'s Stadium, Santa Clara'],
            ['J', 'ARG', 'AUT', '2026-06-22 17:00', 'AT&T Stadium, Arlington'],
            ['J', 'JOR', 'ALG', '2026-06-23 03:00', 'Levi\'s Stadium, Santa Clara'],
            ['J', 'JOR', 'ARG', '2026-06-28 02:00', 'AT&T Stadium, Arlington'],
            ['J', 'ALG', 'AUT', '2026-06-28 02:00', 'Arrowhead Stadium, Kansas City'],

            // GROUP K
            ['K', 'POR', 'COD', '2026-06-17 17:00', 'NRG Stadium, Houston'],
            ['K', 'UZB', 'COL', '2026-06-18 02:00', 'Estadio Azteca, Mexico City'],
            ['K', 'POR', 'UZB', '2026-06-23 17:00', 'NRG Stadium, Houston'],
            ['K', 'COL', 'COD', '2026-06-24 02:00', 'Estadio Akron, Zapopan'],
            ['K', 'COL', 'POR', '2026-06-27 23:30', 'Hard Rock Stadium, Miami Gardens'],
            ['K', 'COD', 'UZB', '2026-06-27 23:30', 'Mercedes-Benz Stadium, Atlanta'],

            // GROUP L
            ['L', 'ENG', 'CRO', '2026-06-17 20:00', 'AT&T Stadium, Arlington'],
            ['L', 'GHA', 'PAN', '2026-06-17 23:00', 'BMO Field, Toronto'],
            ['L', 'ENG', 'GHA', '2026-06-23 20:00', 'Gillette Stadium, Foxborough'],
            ['L', 'PAN', 'CRO', '2026-06-23 23:00', 'BMO Field, Toronto'],
            ['L', 'PAN', 'ENG', '2026-06-27 21:00', 'MetLife Stadium, East Rutherford'],
            ['L', 'CRO', 'GHA', '2026-06-27 21:00', 'Lincoln Financial Field, Philadelphia'],
        ];

        $now = now();
        $rows = [];

        foreach ($fixtures as [$group, $home, $away, $kickoff, $venue]) {
            $rows[] = [
                'stage'            => 'group',
                'group_letter'     => $group,
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
    }
}
