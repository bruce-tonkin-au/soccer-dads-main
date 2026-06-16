<?php

namespace App\Console\Concerns;

use App\Models\WcCard;
use App\Models\WcFixture;
use App\Models\WcPlayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Shared card-event handling for the World Cup commands (wc:sync-results and
 * wc:backfill-cards). Keeps the API → wc_cards logic — and the fuzzy player
 * matching it depends on — in one place so both commands behave identically.
 */
trait SyncsWcCards
{
    /**
     * Insert wc_cards rows for a fixture's card events, returning
     * [insertedCount, [unmatchedPlayerNames]].
     *
     * De-duplicated against existing rows so it is safe to call repeatedly while
     * live (additive only). When $rebuild is true (final whistle) the fixture's
     * cards are cleared first so the API is authoritative.
     *
     * A 2nd yellow that produces a red ('Yellow Red Card') is stored as a single
     * red row with is_second_yellow = true — it counts as a red only, never an
     * extra yellow.
     *
     * @param  array<int, array<string, mixed>>  $events
     * @return array{0:int,1:array<int,string>}
     */
    protected function syncCards(WcFixture $fixture, array $events, bool $rebuild = false): array
    {
        $candidates = WcPlayer::query()
            ->when(
                $fixture->home_team_id || $fixture->away_team_id,
                fn ($q) => $q->whereIn('teamID', array_filter([$fixture->home_team_id, $fixture->away_team_id])),
            )
            ->get(['playerID', 'teamID', 'name']);

        $missing = [];

        // Resolve each API card event to a normalised row.
        $cards = [];
        foreach ($events as $event) {
            if (($event['type'] ?? null) !== 'Card') {
                continue;
            }

            $detail = $event['detail'] ?? '';
            [$type, $isSecondYellow] = match ($detail) {
                'Yellow Card'     => ['yellow', false],
                'Red Card'        => ['red', false],
                'Yellow Red Card' => ['red', true],
                default           => [null, false],
            };
            if ($type === null) {
                continue; // Unknown card detail — skip.
            }

            $playerName = $event['player']['name'] ?? null;
            if (! $playerName) {
                continue;
            }

            $player = $this->matchPlayer($playerName, $candidates);
            if (! $player) {
                $missing[] = $playerName . ' (fixture #' . $fixture->fixtureID . ')';
                Log::warning('wc cards — player not found for card', [
                    'fixtureID'       => $fixture->fixtureID,
                    'api_football_id' => $fixture->api_football_id,
                    'player'          => $playerName,
                    'detail'          => $detail,
                ]);
                continue;
            }

            $cards[] = [
                'playerID'         => $player->playerID,
                'teamID'           => $player->teamID,
                'type'             => $type,
                'is_second_yellow' => $isSecondYellow,
            ];
        }

        $inserted = 0;

        $process = function () use (&$inserted, $fixture, $cards, $rebuild) {
            if ($rebuild) {
                WcCard::where('fixtureID', $fixture->fixtureID)->delete();
            }

            // Insert by shortfall: group by player + type + 2nd-yellow and add
            // only as many rows as the API now reports beyond what is stored.
            $groups = collect($cards)->groupBy(
                fn ($c) => $c['playerID'] . '|' . $c['type'] . '|' . ($c['is_second_yellow'] ? '1' : '0'),
            );

            foreach ($groups as $group) {
                $group = $group->values();
                $first = $group->first();

                $existing = WcCard::where('fixtureID', $fixture->fixtureID)
                    ->where('playerID', $first['playerID'])
                    ->where('type', $first['type'])
                    ->where('is_second_yellow', $first['is_second_yellow'])
                    ->count();

                foreach ($group->slice($existing) as $c) {
                    WcCard::create([
                        'fixtureID'        => $fixture->fixtureID,
                        'playerID'         => $c['playerID'],
                        'teamID'           => $c['teamID'],
                        'type'             => $c['type'],
                        'is_second_yellow' => $c['is_second_yellow'],
                    ]);
                    $inserted++;
                }
            }
        };

        if ($rebuild) {
            DB::transaction($process);
        } else {
            $process();
        }

        return [$inserted, $missing];
    }

    /**
     * Resolve an API player name to one of the fixture's players: exact match,
     * then case-insensitive contains (ILIKE-style), then a last-name / similarity
     * fallback for abbreviated names like "M. Rashford".
     *
     * @param  \Illuminate\Support\Collection<int, WcPlayer>  $candidates
     */
    protected function matchPlayer(string $name, $candidates): ?WcPlayer
    {
        $target = $this->normalizeName($name);

        // 1. Exact (normalised).
        $exact = $candidates->first(fn (WcPlayer $p) => $this->normalizeName($p->name) === $target);
        if ($exact) {
            return $exact;
        }

        // 2. Contains either way (handles "Marcus Rashford" vs "Rashford").
        $contains = $candidates->first(function (WcPlayer $p) use ($target) {
            $cand = $this->normalizeName($p->name);

            return $cand !== '' && (Str::contains($cand, $target) || Str::contains($target, $cand));
        });
        if ($contains) {
            return $contains;
        }

        // 3. Last-name match when unambiguous (e.g. "M. Rashford" -> "Rashford").
        $targetLast = Str::afterLast($target, ' ');
        if ($targetLast !== '') {
            $lastMatches = $candidates->filter(function (WcPlayer $p) use ($targetLast) {
                return Str::afterLast($this->normalizeName($p->name), ' ') === $targetLast;
            });
            if ($lastMatches->count() === 1) {
                return $lastMatches->first();
            }
        }

        // 4. Best similarity over a high threshold.
        $best = null;
        $bestScore = 0.0;
        foreach ($candidates as $p) {
            similar_text($target, $this->normalizeName($p->name), $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $best = $p;
            }
        }

        return $bestScore >= 85.0 ? $best : null;
    }

    protected function normalizeName(string $name): string
    {
        return Str::of($name)->ascii()->lower()->replace('.', ' ')->squish()->value();
    }
}
