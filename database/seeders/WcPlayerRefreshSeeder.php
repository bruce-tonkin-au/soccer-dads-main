<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Wipes wc_players and re-seeds it from database/seeders/data/wc_players.json
 * (the full 2026 FIFA World Cup squads). Run manually:
 *
 *     php artisan db:seed --class=WcPlayerRefreshSeeder --force
 *
 * Note: wc_players is the parent of wc_goals and wc_entry_players (ON DELETE
 * CASCADE), so clearing it also clears any dependent goal/entry-player rows.
 */
class WcPlayerRefreshSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/wc_players.json');
        $players = json_decode(file_get_contents($path), true);

        // team code -> teamID
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        $now = now();
        $rows = [];
        $skipped = [];

        foreach ($players as $player) {
            $teamID = $teamIds[$player['team_code']] ?? null;

            if ($teamID === null) {
                $skipped[] = $player['team_code'];
                continue;
            }

            $rows[] = [
                'teamID' => $teamID,
                'name' => $player['name'],
                'position' => $player['position'] ?? null,
                'shirt_number' => $player['shirt_number'] ?? null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($rows) {
            // delete() rather than truncate(): Postgres refuses to truncate a
            // table referenced by foreign keys, and delete() honours ON DELETE CASCADE.
            DB::table('wc_players')->delete();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('wc_players')->insert($chunk);
            }
        });

        $inserted = DB::table('wc_players')->count();

        $message = "wc_players refreshed: {$inserted} players inserted from " . count($players) . ' JSON rows.';
        if ($skipped) {
            $message .= ' Skipped unknown team codes: ' . implode(', ', array_unique($skipped)) . '.';
        }

        $this->command?->info($message);
    }
}
