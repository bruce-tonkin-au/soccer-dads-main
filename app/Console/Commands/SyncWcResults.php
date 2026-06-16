<?php

namespace App\Console\Commands;

use App\Console\Concerns\MatchesWcPlayers;
use App\Console\Concerns\SyncsWcCards;
use App\Console\Concerns\SyncsWcGoals;
use App\Models\WcFixture;
use App\Support\ApiFootball;
use Illuminate\Console\Command;

class SyncWcResults extends Command
{
    use MatchesWcPlayers;
    use SyncsWcCards;
    use SyncsWcGoals;

    protected $signature = 'wc:sync-results';

    protected $description = 'Pull live/finished World Cup scores and goals from API-Football into wc_fixtures / wc_goals';

    /** API-Football status short codes grouped by what they mean for us. */
    private const STATUS_COMPLETED = ['FT', 'AET', 'PEN'];
    private const STATUS_LIVE      = ['1H', 'HT', '2H', 'ET', 'BT', 'P', 'SUSP', 'INT'];
    private const STATUS_POSTPONED = ['CANC', 'ABD', 'AWD', 'WO'];
    private const STATUS_NOT_STARTED = ['NS', 'TBD'];

    public function handle(ApiFootball $api): int
    {
        $fixtures = WcFixture::query()
            ->where('status', '!=', 'completed')
            ->whereNotNull('api_football_id')
            ->where('match_datetime', '<', now())
            ->get();

        $this->info("Checking {$fixtures->count()} fixture(s) due for an update…");

        $updated = 0;
        $goalsInserted = 0;
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

            $statusShort = $data['fixture']['status']['short'] ?? null;
            $homeScore   = $data['goals']['home'] ?? null;
            $awayScore   = $data['goals']['away'] ?? null;

            if (in_array($statusShort, self::STATUS_NOT_STARTED, true)) {
                continue; // Not started yet — nothing to record.
            }

            if (in_array($statusShort, self::STATUS_POSTPONED, true)) {
                $fixture->update(['status' => 'postponed']);
                $updated++;
                $this->line("Fixture #{$fixture->fixtureID}: postponed ({$statusShort}).");
                continue;
            }

            if (in_array($statusShort, self::STATUS_LIVE, true)) {
                $fixture->update([
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'status'     => 'live',
                ]);
                $updated++;

                // Record goals as they happen — additive only. Never delete
                // mid-match, so a goal can't briefly vanish between polls and
                // the ladder updates in real time.
                [$inserted, $missing] = $this->syncGoals($fixture, $data['events'] ?? [], rebuild: false);
                $goalsInserted += $inserted;
                $playersNotFound = array_merge($playersNotFound, $missing);

                [$cardsAdded, $cardMissing] = $this->syncCards($fixture, $data['events'] ?? [], rebuild: false);
                $cardsInserted += $cardsAdded;
                $playersNotFound = array_merge($playersNotFound, $cardMissing);

                $this->line("Fixture #{$fixture->fixtureID}: live {$homeScore}-{$awayScore} ({$statusShort}); {$inserted} new goal(s), {$cardsAdded} new card(s).");
                continue;
            }

            if (in_array($statusShort, self::STATUS_COMPLETED, true)) {
                $fixture->update([
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'status'     => 'completed',
                ]);
                $updated++;

                // Final whistle — clear and rebuild from the API for an
                // authoritative goal list (reconciles any VAR/corrections).
                [$inserted, $missing] = $this->syncGoals($fixture, $data['events'] ?? [], rebuild: true);
                $goalsInserted += $inserted;
                $playersNotFound = array_merge($playersNotFound, $missing);

                [$cardsAdded, $cardMissing] = $this->syncCards($fixture, $data['events'] ?? [], rebuild: true);
                $cardsInserted += $cardsAdded;
                $playersNotFound = array_merge($playersNotFound, $cardMissing);

                $this->line("Fixture #{$fixture->fixtureID}: completed {$homeScore}-{$awayScore}; {$inserted} goal(s), {$cardsAdded} card(s) recorded.");
                continue;
            }

            $this->warn("Fixture #{$fixture->fixtureID}: unhandled status '{$statusShort}'.");
        }

        $this->newLine();
        $summary = sprintf(
            '%d fixture(s) updated, %d goal(s) inserted, %d card(s) inserted, %d player(s) not found.',
            $updated,
            $goalsInserted,
            $cardsInserted,
            count($playersNotFound),
        );
        $this->info($summary);

        if (! empty($playersNotFound)) {
            $this->warn('Players not found (add manually, then re-run):');
            foreach (array_unique($playersNotFound) as $name) {
                $this->line('  • ' . $name);
            }
        }

        return self::SUCCESS;
    }
}
