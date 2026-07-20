<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WcThirdPlaceSeeder extends Seeder
{
    public function run(): void
    {
        // Safe to re-run: bail out if the Third-place playoff has already been
        // seeded (these inserts hit the production DB).
        if (DB::table('wc_fixtures')->where('stage', 'third_place')->exists()) {
            $this->command?->warn('Third-place playoff fixture already present — skipping.');

            return;
        }

        // Map team code -> teamID for resolving home/away foreign keys.
        $teamIds = DB::table('wc_teams')->pluck('teamID', 'code');

        // [home_code, away_code, kick-off (UTC), venue]. No group_letter — knockout.
        // Home/away order mirrors the seeder convention (first = home). England
        // is home so the admin result entry reads England 6 : 4 France.
        $fixtures = [
            ['ENG', 'FRA', '2026-07-18 21:00', 'Hard Rock Stadium, Miami Gardens'],
        ];

        $now = now();
        $rows = [];

        foreach ($fixtures as [$home, $away, $kickoff, $venue]) {
            $rows[] = [
                // stage = 'third_place' is deliberately NOT one of the six rounds
                // the admin form Select offers, but the wc_fixtures.stage column
                // is a free string(20) (no enum/check constraint) so the insert is
                // valid. Why this value is safe:
                //   - Results tab (WcResults::buildResults) queries by status =
                //     'completed' with no stage filter, so once the result is
                //     entered the match shows in Results and awards goal-points
                //     exactly like every other fixture.
                //   - The progress bar (WcPage::tournamentProgress) iterates a
                //     FIXED whitelist (WcPage::STAGES: group..final). 'third_place'
                //     is absent from that map, so the bar never renders it as a
                //     node, never counts it, and never treats it as "current" —
                //     the Final stays the last step. Exclusion is by omission.
                // NOTE: before entering scorers via admin, add 'third_place' to the
                // stage Select options in WcFixtureForm.php so the required stage
                // field resolves on edit-save (see the investigation notes).
                'stage'            => 'third_place',
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

        $this->command?->info('Seeded ' . count($rows) . ' Third-place playoff fixture.');
    }
}
