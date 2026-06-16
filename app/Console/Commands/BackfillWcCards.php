<?php

namespace App\Console\Commands;

use App\Console\Concerns\MatchesWcPlayers;
use App\Console\Concerns\SyncsWcCards;
use App\Models\WcFixture;
use App\Support\ApiFootball;
use Illuminate\Console\Command;

class BackfillWcCards extends Command
{
    use MatchesWcPlayers;
    use SyncsWcCards;

    protected $signature = 'wc:backfill-cards';

    protected $description = 'Backfill wc_cards from already-completed World Cup fixtures by re-fetching their card events from API-Football';

    public function handle(ApiFootball $api): int
    {
        $fixtures = WcFixture::query()
            ->where('status', 'completed')
            ->whereNotNull('api_football_id')
            ->orderBy('match_datetime')
            ->get();

        $this->info("Backfilling cards for {$fixtures->count()} completed fixture(s)…");

        $cardsInserted = 0;
        $playersNotFound = [];

        foreach ($fixtures as $fixture) {
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

            // Additive backfill — never wipe; insert only the rows still missing.
            [$inserted, $missing] = $this->syncCards($fixture, $data['events'] ?? [], rebuild: false);
            $cardsInserted += $inserted;
            $playersNotFound = array_merge($playersNotFound, $missing);

            if ($inserted > 0) {
                $this->line("Fixture #{$fixture->fixtureID}: {$inserted} card(s) inserted.");
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%d card(s) inserted, %d player(s) not found.',
            $cardsInserted,
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
