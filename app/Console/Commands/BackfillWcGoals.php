<?php

namespace App\Console\Commands;

use App\Console\Concerns\MatchesWcPlayers;
use App\Console\Concerns\SyncsWcGoals;
use App\Models\WcFixture;
use App\Models\WcGoal;
use App\Support\ApiFootball;
use Illuminate\Console\Command;

class BackfillWcGoals extends Command
{
    use MatchesWcPlayers;
    use SyncsWcGoals;

    protected $signature = 'wc:backfill-goals';

    protected $description = 'Self-healing sweep: re-fetch goals for completed World Cup fixtures that have a non-0-0 score but no recorded wc_goals rows';

    public function handle(ApiFootball $api): int
    {
        $fixtures = WcFixture::query()
            ->where('status', 'completed')
            ->whereNotNull('api_football_id')
            ->orderBy('match_datetime')
            ->get();

        $this->info("Checking {$fixtures->count()} completed fixture(s) for missing goals…");

        $fixturesFixed = 0;
        $goalsInserted = 0;
        $playersNotFound = [];

        foreach ($fixtures as $fixture) {
            // Already has goals — nothing to heal. Idempotent re-runs skip here.
            if (WcGoal::where('fixtureID', $fixture->fixtureID)->exists()) {
                continue;
            }

            // A legitimately goalless game (0-0, or scores not yet known) has no
            // goals to fetch — don't waste an API call on it.
            if ((int) $fixture->home_score + (int) $fixture->away_score === 0) {
                continue;
            }

            try {
                $data = $api->fixtureById((int) $fixture->api_football_id);
            } catch (\Throwable $e) {
                $this->error("Fixture #{$fixture->fixtureID}: API request failed — {$e->getMessage()}");
                continue;
            }

            if (! $data) {
                $this->warn("Fixture #{$fixture->fixtureID}: no API response for id {$fixture->api_football_id}.");
                continue;
            }

            // Process exactly as wc:sync-results does for a completed fixture:
            // authoritative rebuild from the API (safe — we only reach here when
            // the fixture has zero goals).
            [$inserted, $missing] = $this->syncGoals($fixture, $data['events'] ?? [], rebuild: true);
            $goalsInserted += $inserted;
            $playersNotFound = array_merge($playersNotFound, $missing);

            if ($inserted > 0) {
                $fixturesFixed++;
                $this->line("Fixture #{$fixture->fixtureID}: {$fixture->home_score}-{$fixture->away_score}; {$inserted} goal(s) inserted.");
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%d fixture(s) fixed, %d goal(s) inserted, %d player(s) not found.',
            $fixturesFixed,
            $goalsInserted,
            count($playersNotFound),
        ));

        if (! empty($playersNotFound)) {
            $this->warn('Players not found (add manually, then re-run):');
            foreach (array_unique($playersNotFound) as $name) {
                $this->line('  • ' . $name);
            }
        }

        return self::SUCCESS;
    }
}
