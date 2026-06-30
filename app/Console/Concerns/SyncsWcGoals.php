<?php

namespace App\Console\Concerns;

use App\Models\WcFixture;
use App\Models\WcGoal;
use App\Models\WcPlayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Shared goal-event handling for the World Cup commands (wc:sync-results and
 * wc:backfill-goals). Keeps the API → wc_goals logic in one place so both
 * commands behave identically. The consuming class must also use
 * {@see MatchesWcPlayers}, which supplies matchPlayer().
 */
trait SyncsWcGoals
{
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
    protected function syncGoals(WcFixture $fixture, array $events, bool $rebuild = false): array
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

            $detail   = $event['detail'] ?? '';
            $comments = (string) ($event['comments'] ?? '');
            if ($detail === 'Missed Penalty') {
                continue;
            }

            // Shootout goals are stored but flagged: wc_fixtures stores the
            // pre-shootout (90+ET) score, so points/ladder code filters them
            // out via is_shootout = false — but they still appear on the
            // results page with a (pen) marker. API-Football emits them in
            // the same events array with comments = 'Penalty Shootout' (and
            // usually a null elapsed minute); some responses also use detail.
            $isShootout = ($detail === 'Penalty Shootout'
                || stripos($comments, 'Penalty Shootout') !== false);

            $playerName = $event['player']['name'] ?? null;
            if (! $playerName) {
                continue;
            }

            $player = $this->matchPlayer($playerName, $candidates);
            if (! $player) {
                $missing[] = $playerName . ' (fixture #' . $fixture->fixtureID . ')';
                Log::warning('wc goals — player not found for goal', [
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
                'is_shootout' => $isShootout,
            ];
        }

        $inserted = 0;

        $process = function () use (&$inserted, $fixture, $goals, $rebuild) {
            // Completed rebuild: wipe first so removed/overturned goals don't
            // linger. Live: leave existing rows in place.
            if ($rebuild) {
                WcGoal::where('fixtureID', $fixture->fixtureID)->delete();
            }

            // Insert by shortfall, not existence: group by player + own-goal +
            // shootout and add only as many rows as the API now reports beyond
            // what is already stored. So a player who scored twice gets two
            // rows, while re-polling a live match never duplicates. Shootout
            // is part of the key so a player's regular goal and shootout pen
            // don't collapse into one bucket.
            $groups = collect($goals)->groupBy(fn ($g) => $g['playerID']
                . '|' . ($g['is_own_goal'] ? '1' : '0')
                . '|' . ($g['is_shootout'] ? '1' : '0'));

            foreach ($groups as $group) {
                $group = $group->values();
                $first = $group->first();

                $existing = WcGoal::where('fixtureID', $fixture->fixtureID)
                    ->where('playerID', $first['playerID'])
                    ->where('is_own_goal', $first['is_own_goal'])
                    ->where('is_shootout', $first['is_shootout'])
                    ->count();

                foreach ($group->slice($existing) as $g) {
                    WcGoal::create([
                        'fixtureID'   => $fixture->fixtureID,
                        'playerID'    => $g['playerID'],
                        'teamID'      => $g['teamID'],
                        'minute'      => $g['minute'],
                        'is_own_goal' => $g['is_own_goal'],
                        'is_shootout' => $g['is_shootout'],
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
    protected function benefitingTeamId(WcFixture $fixture, ?int $scorerTeamId): ?int
    {
        if ($scorerTeamId !== null && (int) $scorerTeamId === (int) $fixture->home_team_id) {
            return $fixture->away_team_id;
        }
        if ($scorerTeamId !== null && (int) $scorerTeamId === (int) $fixture->away_team_id) {
            return $fixture->home_team_id;
        }

        return $scorerTeamId; // Scorer not on either side — leave unchanged.
    }
}
