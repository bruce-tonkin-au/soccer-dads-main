<?php

namespace App\Livewire;

use App\Models\WcEntry;
use App\Models\WcEntryPlayer;
use App\Models\WcEntryTeam;
use App\Models\WcFixture;
use App\Models\WcGoal;
use App\Models\WcPlayer;
use App\Models\WcSetting;
use App\Models\WcTeam;
use App\Support\MemberDirectory;
use Illuminate\Support\Collection;
use Livewire\Component;

class WorldCupLadder extends Component
{
    public string $activeTab = 'ladder';

    public function mount(): void
    {
        // Nothing heavy — all data is loaded in render().
    }

    public function render()
    {
        $pointsKey = $this->pointsKey();
        $drawRun = WcEntryTeam::query()->exists();

        // Shared bulk loads (no per-entry / per-fixture queries below).
        $entries = WcEntry::query()
            ->with(['entryTeams', 'entryPlayers'])
            ->orderBy('entryID')
            ->get();

        $teamIds = $entries->flatMap(fn (WcEntry $e) => $e->entryTeams->pluck('teamID'))->unique();
        $playerIds = $entries->flatMap(fn (WcEntry $e) => $e->entryPlayers->pluck('playerID'))->unique();
        $teams = WcTeam::whereIn('teamID', $teamIds)->get()->keyBy('teamID');
        $players = WcPlayer::with('team')->whereIn('playerID', $playerIds)->get()->keyBy('playerID');

        // Member display names ("Last, First"), resolved in one query.
        $memberNames = MemberDirectory::labels($entries->pluck('memberID')->all());

        $teamPoints = $this->teamPointsMap($pointsKey);
        $goalCounts = WcGoal::query()
            ->where('is_own_goal', false)
            ->selectRaw('"playerID", count(*) as goals')
            ->groupBy('playerID')
            ->pluck('goals', 'playerID');

        $enriched = $entries->map(fn (WcEntry $e) => $this->entryRow($e, $teams, $players, $teamPoints, $goalCounts, $pointsKey, $memberNames));

        return view('livewire.world-cup-ladder', [
            'activeTab' => $this->activeTab,
            'drawRun' => $drawRun,
            'pointsKey' => $pointsKey,
            'liveFixtures' => $this->liveFixtures(),
            'ladder' => $this->ladder($enriched, $drawRun),
            'results' => $this->buildResults($enriched, $teams, $players, $pointsKey),
            'upcoming' => $this->buildUpcoming($enriched, $teams),
        ])
            ->extends('layouts.app')
            ->section('content');
    }

    /**
     * @return array{team_goal:int,player_goal:int}
     */
    protected function pointsKey(): array
    {
        $values = WcSetting::query()
            ->whereIn('key', ['points_team_goal', 'points_player_goal'])
            ->pluck('value', 'key');

        return [
            'team_goal' => (int) ($values['points_team_goal'] ?? 1),
            'player_goal' => (int) ($values['points_player_goal'] ?? 1),
        ];
    }

    /**
     * Build one enriched row per entry (teams, players, points). Works before
     * the draw too — entries simply have null teams / no players / zero points.
     */
    protected function entryRow(WcEntry $entry, Collection $teams, Collection $players, Collection $teamPoints, $goalCounts, array $points, array $memberNames): array
    {
        $topTeamId = $entry->entryTeams->firstWhere('tier', 1)?->teamID;
        $bottomTeamId = $entry->entryTeams->firstWhere('tier', 2)?->teamID;

        $teamIds = array_values(array_filter([$topTeamId, $bottomTeamId]));
        $teamPts = collect($teamIds)->sum(fn ($id) => $teamPoints[$id] ?? 0);

        $playerRows = $entry->entryPlayers
            ->sortBy('slot')
            ->map(function ($ep) use ($players, $goalCounts) {
                $player = $players[$ep->playerID] ?? null;

                return [
                    'playerID' => $ep->playerID,
                    'name' => $player?->name ?? '—',
                    'flag' => $player?->team?->flag,
                    'team_id' => $player?->teamID,
                    'team_name' => $player?->team?->name,
                    'goal_count' => (int) ($goalCounts[$ep->playerID] ?? 0),
                ];
            })
            ->values();

        $playerPts = $playerRows->sum('goal_count') * $points['player_goal'];

        return [
            'entryID' => $entry->entryID,
            'entry_name' => $entry->entry_name,
            'member_name' => $memberNames[$entry->memberID] ?? $entry->entry_name,
            'draw_completed' => (bool) $entry->draw_completed,
            'top_team' => $this->teamRow($teams[$topTeamId] ?? null),
            'bottom_team' => $this->teamRow($teams[$bottomTeamId] ?? null),
            'players' => $playerRows->all(),
            'team_ids' => $teamIds,
            'player_ids' => $playerRows->pluck('playerID')->all(),
            'team_points' => $teamPts,
            'player_points' => $playerPts,
            'total_points' => $teamPts + $playerPts,
        ];
    }

    /**
     * Ranked ladder. Sorted by points once the draw has run (with positions /
     * medals), otherwise left in creation order with no position.
     */
    protected function ladder(Collection $enriched, bool $drawRun): Collection
    {
        if (! $drawRun) {
            return $enriched
                ->sortBy(fn (array $row) => mb_strtolower($row['member_name']))
                ->values()
                ->map(function (array $row) {
                    $row['position'] = null;

                    return $row;
                });
        }

        return $enriched
            ->sortByDesc('total_points')
            ->values()
            ->map(function (array $row, int $index) {
                $row['position'] = $index + 1;

                return $row;
            });
    }

    /**
     * Points earned by each team: one point (× points_team_goal) for every goal
     * credited to the team. Own goals ARE included — wc_goals.teamID already
     * holds the team that benefits, so grouping by teamID counts each team's
     * goals-for (matching the scoreline). Bulk-counted in a single query.
     *
     * @param  array{team_goal:int,player_goal:int}  $points
     */
    protected function teamPointsMap(array $points): Collection
    {
        return WcGoal::query()
            ->selectRaw('"teamID", count(*) as goals')
            ->groupBy('teamID')
            ->pluck('goals', 'teamID')
            ->map(fn ($goals) => (int) $goals * $points['team_goal']);
    }

    protected function teamRow(?WcTeam $team): ?array
    {
        if (! $team) {
            return null;
        }

        return [
            'flag' => $team->flag,
            'name' => $team->name,
            'code' => $team->code,
            'group_letter' => $team->group_letter,
        ];
    }

    /**
     * All completed fixtures (newest first) with scorers and a per-match
     * breakdown of which entries gained points and why.
     *
     * @param  array{team_goal:int,player_goal:int}  $points
     */
    protected function buildResults(Collection $enriched, Collection $teams, Collection $players, array $points): Collection
    {
        $fixtures = WcFixture::query()
            ->where('status', 'completed')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->with(['homeTeam', 'awayTeam'])
            ->orderByDesc('match_datetime')
            ->get();

        if ($fixtures->isEmpty()) {
            return collect();
        }

        $goalsByFixture = WcGoal::query()
            ->whereIn('fixtureID', $fixtures->pluck('fixtureID'))
            ->with('player')
            ->get()
            ->groupBy('fixtureID');

        return $fixtures->map(function (WcFixture $fixture) use ($enriched, $teams, $players, $points, $goalsByFixture) {
            $goals = $goalsByFixture[$fixture->fixtureID] ?? collect();

            $awards = [];

            // Team-goal points: per team credited with a goal this match. Own
            // goals ARE included — wc_goals.teamID holds the benefiting team, so
            // grouping by teamID matches the scoreline and the ladder total.
            $byTeam = $goals->groupBy('teamID');
            foreach ($byTeam as $teamId => $teamGoals) {
                $count = $teamGoals->count();
                $teamName = $teams[$teamId]->name ?? 'Team';

                foreach ($enriched as $entry) {
                    if (! in_array($teamId, $entry['team_ids'])) {
                        continue;
                    }
                    $awards[] = [
                        'member_name' => $entry['member_name'],
                        'points' => $count * $points['team_goal'],
                        'reason' => $count === 1 ? "{$teamName} goal" : "{$teamName} {$count} goals",
                    ];
                }
            }

            // Player-goal points: per scorer (non own goal) in this match.
            $byScorer = $goals->where('is_own_goal', false)->groupBy('playerID');
            foreach ($byScorer as $playerId => $playerGoals) {
                $count = $playerGoals->count();
                $playerName = $players[$playerId]?->name ?? $playerGoals->first()->player?->name ?? 'Player';

                foreach ($enriched as $entry) {
                    if (! in_array($playerId, $entry['player_ids'])) {
                        continue;
                    }
                    $awards[] = [
                        'member_name' => $entry['member_name'],
                        'points' => $count * $points['player_goal'],
                        'reason' => $count === 1 ? "{$playerName} goal" : "{$playerName} {$count} goals",
                    ];
                }
            }

            return [
                'date' => $fixture->match_datetime,
                'group_letter' => $fixture->group_letter,
                'home_flag' => $fixture->homeTeam?->flag,
                'home_name' => $fixture->homeTeam?->name ?? $fixture->home_placeholder,
                'away_flag' => $fixture->awayTeam?->flag,
                'away_name' => $fixture->awayTeam?->name ?? $fixture->away_placeholder,
                'home_score' => $fixture->home_score,
                'away_score' => $fixture->away_score,
                'scorers' => $this->scorerLine($goals),
                'awards' => $awards,
            ];
        });
    }

    /**
     * Human-readable scorer line for a fixture, e.g. "Jiménez (2), Giménez · OG: Tau".
     */
    protected function scorerLine(Collection $goals): string
    {
        $normal = $goals->where('is_own_goal', false)
            ->groupBy('playerID')
            ->map(function ($group) {
                $name = $group->first()->player?->name ?? 'Player';
                $count = $group->count();

                return $count > 1 ? "{$name} ({$count})" : $name;
            })
            ->values();

        $own = $goals->where('is_own_goal', true)
            ->map(fn (WcGoal $g) => $g->player?->name ?? 'Player')
            ->values();

        $parts = [];
        if ($normal->isNotEmpty()) {
            $parts[] = $normal->implode(', ');
        }
        if ($own->isNotEmpty()) {
            $parts[] = 'OG: ' . $own->implode(', ');
        }

        return implode(' · ', $parts);
    }

    /**
     * Currently in-play fixtures (status = 'live'), each annotated with the
     * entries that have a stake — a team in the match, or a player on the pitch.
     * Stakes are grouped by team / player and only count fully-drawn entries.
     * Uses the already-loaded $enriched collection — no per-fixture queries.
     */
    protected function liveFixtures(): Collection
    {
        $fixtures = WcFixture::query()
            ->where('status', 'live')
            ->with(['homeTeam', 'awayTeam', 'goals.player'])
            ->orderBy('match_datetime')
            ->get();

        if ($fixtures->isEmpty()) {
            return collect();
        }

        // Every team taking part in a live fixture.
        $liveTeamIds = $fixtures
            ->flatMap(fn (WcFixture $f) => array_filter([$f->home_team_id, $f->away_team_id]))
            ->unique()
            ->values();

        // Stake data is queried straight from wc_entry_teams / wc_entry_players
        // (drawn entries only) rather than relying on the render-time $enriched
        // collection, so live stakes show regardless of which tab is active.
        $teamStakeRows = WcEntryTeam::query()
            ->whereIn('teamID', $liveTeamIds)
            ->whereHas('entry', fn ($q) => $q->where('draw_completed', true))
            ->get(['entryID', 'teamID']);

        // Players belonging to a live team, so a fixture can be matched via the
        // player's own team even when the entry didn't draw that team directly.
        $players = WcPlayer::query()
            ->whereIn('teamID', $liveTeamIds)
            ->get(['playerID', 'teamID', 'name'])
            ->keyBy('playerID');

        $playerStakeRows = WcEntryPlayer::query()
            ->whereIn('playerID', $players->keys())
            ->whereHas('entry', fn ($q) => $q->where('draw_completed', true))
            ->get(['entryID', 'playerID']);

        // Resolve team names and member display labels in bulk.
        $teams = WcTeam::whereIn('teamID', $liveTeamIds)->get()->keyBy('teamID');

        $entryIds = $teamStakeRows->pluck('entryID')
            ->merge($playerStakeRows->pluck('entryID'))
            ->unique()
            ->values();
        $entries = WcEntry::whereIn('entryID', $entryIds)
            ->get(['entryID', 'memberID', 'entry_name'])
            ->keyBy('entryID');
        $memberNames = MemberDirectory::labels($entries->pluck('memberID')->all());

        $nameFor = function ($entryID) use ($entries, $memberNames) {
            $entry = $entries[$entryID] ?? null;

            return $entry ? ($memberNames[$entry->memberID] ?? $entry->entry_name) : null;
        };

        return $fixtures->map(function (WcFixture $fixture) use ($teamStakeRows, $playerStakeRows, $players, $teams, $nameFor) {
            $fixtureTeamIds = array_values(array_filter([$fixture->home_team_id, $fixture->away_team_id]));

            $teamGroups = [];   // teamID   => ['team' => name, 'names' => [...]]
            $playerGroups = []; // playerID => ['player' => name, 'names' => [...]]

            foreach ($teamStakeRows as $row) {
                if (! in_array($row->teamID, $fixtureTeamIds)) {
                    continue;
                }
                $name = $nameFor($row->entryID);
                if ($name === null) {
                    continue;
                }
                $teamGroups[$row->teamID]['team'] = $teams[$row->teamID]->name ?? '';
                $teamGroups[$row->teamID]['names'][] = $name;
            }

            foreach ($playerStakeRows as $row) {
                $player = $players[$row->playerID] ?? null;
                if ($player === null || ! in_array($player->teamID, $fixtureTeamIds)) {
                    continue;
                }
                $name = $nameFor($row->entryID);
                if ($name === null) {
                    continue;
                }
                $playerGroups[$row->playerID]['player'] = $player->name;
                $playerGroups[$row->playerID]['names'][] = $name;
            }

            return [
                'group_letter' => $fixture->group_letter,
                'home_flag' => $fixture->homeTeam?->flag,
                'home_name' => $fixture->homeTeam?->name ?? $fixture->home_placeholder ?? 'TBD',
                'away_flag' => $fixture->awayTeam?->flag,
                'away_name' => $fixture->awayTeam?->name ?? $fixture->away_placeholder ?? 'TBD',
                'home_score' => $fixture->home_score ?? 0,
                'away_score' => $fixture->away_score ?? 0,
                'scorers' => $this->scorerLine($fixture->goals),
                'team_stakes' => array_values($teamGroups),
                'player_stakes' => array_values($playerGroups),
            ];
        });
    }

    /**
     * All remaining scheduled fixtures (soonest first), each annotated with the
     * entries that have a team — or a player — involved in the match.
     */
    protected function buildUpcoming(Collection $enriched, Collection $teams): Collection
    {
        $fixtures = WcFixture::query()
            ->where('status', 'scheduled')
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('match_datetime')
            ->limit(10)
            ->get();

        return $fixtures->map(function (WcFixture $fixture) use ($enriched, $teams) {
            $fixtureTeamIds = array_values(array_filter([$fixture->home_team_id, $fixture->away_team_id]));

            $teamWatchers = [];
            $playerWatchers = [];

            // $enriched is already in memory — no per-fixture queries.
            foreach ($enriched as $entry) {
                foreach ($entry['team_ids'] as $teamId) {
                    if (in_array($teamId, $fixtureTeamIds)) {
                        $teamWatchers[] = [
                            'name' => $entry['member_name'],
                            'team' => $teams[$teamId]->name ?? '',
                        ];
                    }
                }

                foreach ($entry['players'] as $player) {
                    if ($player['team_id'] !== null && in_array($player['team_id'], $fixtureTeamIds)) {
                        $playerWatchers[] = [
                            'name' => $entry['member_name'],
                            'player' => $player['name'],
                        ];
                    }
                }
            }

            return [
                'datetime' => $fixture->match_datetime,
                'group_letter' => $fixture->group_letter,
                'home_flag' => $fixture->homeTeam?->flag,
                'home_name' => $fixture->homeTeam?->name ?? $fixture->home_placeholder ?? 'TBD',
                'away_flag' => $fixture->awayTeam?->flag,
                'away_name' => $fixture->awayTeam?->name ?? $fixture->away_placeholder ?? 'TBD',
                'team_watchers' => $teamWatchers,
                'player_watchers' => $playerWatchers,
            ];
        });
    }
}
