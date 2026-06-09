<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WcPlayerSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/wc_players.json');
        $players = json_decode(file_get_contents($path), true);

        // Map team code -> teamID so we can resolve the foreign key.
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        $now = now();
        $rows = [];

        foreach ($players as $player) {
            $teamID = $teamIds[$player['team_code']] ?? null;

            if ($teamID === null) {
                $this->command?->warn("Skipping {$player['name']}: unknown team code {$player['team_code']}");
                continue;
            }

            $rows[] = [
                'teamID'       => $teamID,
                'name'         => $player['name'],
                'position'     => $player['position'] ?? null,
                'shirt_number' => $player['shirt_number'] ?? null,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        DB::table('wc_players')->insertOrIgnore($rows);
    }
}
