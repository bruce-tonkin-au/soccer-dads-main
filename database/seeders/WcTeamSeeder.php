<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WcTeamSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/wc_teams.json');
        $teams = json_decode(file_get_contents($path), true);

        $now = now();
        $rows = [];

        foreach ($teams as $team) {
            $rows[] = [
                'name'           => $team['name'],
                'code'           => $team['code'],
                'flag'           => $team['flag'] ?? null,
                'confederation'  => $team['confederation'],
                'fifa_ranking'   => $team['fifa_ranking'] ?? null,
                'pot'            => $team['pot'],
                'seed_tier'      => $team['seed_tier'],
                'group_letter'   => $team['group_letter'] ?? null,
                'group_position' => $team['group_position'] ?? null,
                'qualified'      => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        DB::table('wc_teams')->insertOrIgnore($rows);
    }
}
