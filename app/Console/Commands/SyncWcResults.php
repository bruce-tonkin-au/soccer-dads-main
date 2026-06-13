<?php

namespace App\Console\Commands;

use App\Models\WcFixture;
use App\Models\WcGoal;
use App\Models\WcPlayer;
use App\Support\ApiFootball;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncWcResults extends Command
{
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

                $this->line("Fixture #{$fixture->fixtureID}: live {$homeScore}-{$awayScore} ({$statusShort}); {$inserted} new goal(s).");
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

                $this->line("Fixture #{$fixture->fixtureID}: completed {$homeScore}-{$awayScore}; {$inserted} goal(s) recorded.");
                continue;
            }

            $this->warn("Fixture #{$fixture->fixtureID}: unhandled status '{$statusShort}'.");
        }

        $this->newLine();
        $summary = sprintf(
            '%d fixture(s) updated, %d goal(s) inserted, %d player(s) not found.',
            $updated,
            $goalsInserted,
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

    /**
     * Insert wc_goals rows for a fixture's goal events, returning
     * [insertedCount, [unmatchedPlayerNames]].
     *
     * Inserts are de-duplicated against existing rows, so this is safe to call
     * repeatedly while a match is live. When $rebuild is true (final whistle)
     * the fixture's goals are first cleared inside a transaction so the API is
     * authoritative; when false (live) goals are only ever added, never removed.
     *
     * @param  array<int, array<string, mixed>>  $events
     * @return array{0:int,1:array<int,string>}
     */
    private function syncGoals(WcFixture $fixture, array $events, bool $rebuild = false): array
    {
        $candidates = WcPlayer::query()
            ->when(
                $fixture->home_team_id || $fixture->away_team_id,
                fn ($q) => $q->whereIn('teamID', array_filter([$fixture->home_team_id, $fixture->away_team_id])),
            )
            ->get(['playerID', 'teamID', 'name']);

        $missing = [];

        // Resolve each API goal event to a normalised row. For an own goal the
        // teamID is the OPPONENT — the side that benefits from / is credited
        // with the goal — not the scorer's own team.
        $goals = [];
        foreach ($events as $event) {
            if (($event['type'] ?? null) !== 'Goal') {
                continue;
            }

            $detail = $event['detail'] ?? '';
            if ($detail === 'Missed Penalty') {
                continue;
            }

            $playerName = $event['player']['name'] ?? null;
            if (! $playerName) {
                continue;
            }

            $player = $this->matchPlayer($playerName, $candidates);
            if (! $player) {
                $missing[] = $playerName . ' (fixture #' . $fixture->fixtureID . ')';
                Log::warning('wc:sync-results — player not found for goal', [
                    'fixtureID'       => $fixture->fixtureID,
                    'api_football_id' => $fixture->api_football_id,
                    'player'          => $playerName,
                    'detail'          => $detail,
                ]);
                continue;
            }

            $isOwnGoal = $detail === 'Own Goal';
            $minute    = $event['time']['elapsed'] ?? null;
            if (isset($event['time']['extra']) && $event['time']['extra'] !== null && $minute !== null) {
                $minute += (int) $event['time']['extra'];
            }

            $goals[] = [
                'playerID'    => $player->playerID,
                'teamID'      => $isOwnGoal ? $this->benefitingTeamId($fixture, $player->teamID) : $player->teamID,
                'minute'      => $minute,
                'is_own_goal' => $isOwnGoal,
            ];
        }

        $inserted = 0;

        $process = function () use (&$inserted, $fixture, $goals, $rebuild) {
            // Completed rebuild: wipe first so removed/overturned goals don't
            // linger. Live: leave existing rows in place.
            if ($rebuild) {
                WcGoal::where('fixtureID', $fixture->fixtureID)->delete();
            }

            // Insert by shortfall, not existence: group by player + own-goal and
            // add only as many rows as the API now reports beyond what is already
            // stored. So a player who scored twice gets two rows, while
            // re-polling a live match never duplicates.
            $groups = collect($goals)->groupBy(fn ($g) => $g['playerID'] . '|' . ($g['is_own_goal'] ? '1' : '0'));

            foreach ($groups as $group) {
                $group = $group->values();
                $first = $group->first();

                $existing = WcGoal::where('fixtureID', $fixture->fixtureID)
                    ->where('playerID', $first['playerID'])
                    ->where('is_own_goal', $first['is_own_goal'])
                    ->count();

                foreach ($group->slice($existing) as $g) {
                    WcGoal::create([
                        'fixtureID'   => $fixture->fixtureID,
                        'playerID'    => $g['playerID'],
                        'teamID'      => $g['teamID'],
                        'minute'      => $g['minute'],
                        'is_own_goal' => $g['is_own_goal'],
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
     * The team credited with a goal scored by a player on $scorerTeamId. For a
     * normal goal that is the scorer's team; this resolves the OWN-GOAL case,
     * where the credited team is the opponent in this fixture.
     */
    private function benefitingTeamId(WcFixture $fixture, ?int $scorerTeamId): ?int
    {
        if ($scorerTeamId !== null && (int) $scorerTeamId === (int) $fixture->home_team_id) {
            return $fixture->away_team_id;
        }
        if ($scorerTeamId !== null && (int) $scorerTeamId === (int) $fixture->away_team_id) {
            return $fixture->home_team_id;
        }

        return $scorerTeamId; // Scorer not on either side — leave unchanged.
    }

    /**
     * Resolve an API player name to one of the fixture's players: exact match,
     * then case-insensitive contains (ILIKE-style), then a last-name / similarity
     * fallback for abbreviated names like "M. Rashford".
     *
     * @param  \Illuminate\Support\Collection<int, WcPlayer>  $candidates
     */
    private function matchPlayer(string $name, $candidates): ?WcPlayer
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

    private function normalizeName(string $name): string
    {
        return Str::of($name)->ascii()->lower()->replace('.', ' ')->squish()->value();
    }
}
