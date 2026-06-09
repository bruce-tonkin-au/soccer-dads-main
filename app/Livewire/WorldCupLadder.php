<?php

namespace App\Livewire;

use App\Models\WcEntry;
use App\Models\WcEntryTeam;
use App\Models\WcFixture;
use App\Models\WcGoal;
use App\Models\WcPlayer;
use App\Models\WcSetting;
use App\Models\WcTeam;
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

        $teamPoints = $this->teamPointsMap($pointsKey);
        $goalCounts = WcGoal::query()
            ->where('is_own_goal', false)
            ->selectRaw('"playerID", count(*) as goals')
            ->groupBy('playerID')
            ->pluck('goals', 'playerID');

        $enriched = $entries->map(fn (WcEntry $e) => $this->entryRow($e, $teams, $players, $teamPoints, $goalCounts, $pointsKey));

        return view('livewire.world-cup-ladder', [
            'activeTab' => $this->activeTab,
            'drawRun' => $drawRun,
            'pointsKey' => $pointsKey,
            'ladder' => $this->ladder($enriched, $drawRun),
            'results' => $this->buildResults($enriched, $teams, $players, $pointsKey),
            'upcoming' => $this->buildUpcoming($enriched, $teams),
        ])
            ->extends('layouts.app')
            ->section('content');
    }

    /**
     * @return array{win:int,draw:int,goal:int}
     */
    protected function pointsKey(): array
    {
        $values = WcSetting::query()
            ->whereIn('key', ['points_team_win', 'points_team_draw', 'points_player_goal'])
            ->pluck('value', 'key');

        return [
            'win' => (int) ($values['points_team_win'] ?? 3),
            'draw' => (int) ($values['points_team_draw'] ?? 1),
            'goal' => (int) ($values['points_player_goal'] ?? 2),
        ];
    }

    /**
     * Build one enriched row per entry (teams, players, points). Works before
     * the draw too — entries simply have null teams / no players / zero points.
     */
    protected function entryRow(WcEntry $entry, Collection $teams, Collection $players, Collection $teamPoints, $goalCounts, array $points): array
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

        $playerPts = $playerRows->sum('goal_count') * $points['goal'];

        return [
            'entryID' => $entry->entryID,
            'entry_name' => $entry->entry_name,
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
            return $enriched->values()->map(function (array $row) {
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
     * @param  array{win:int,draw:int,goal:int}  $points
     */
    protected function teamPointsMap(array $points): Collection
    {
        $map = [];

        WcFixture::query()
            ->where('status', 'completed')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->get(['home_team_id', 'away_team_id', 'home_score', 'away_score'])
            ->each(function (WcFixture $fixture) use (&$map, $points) {
                $home = $fixture->home_team_id;
                $away = $fixture->away_team_id;
                $map[$home] ??= 0;
                $map[$away] ??= 0;

                if ($fixture->home_score > $fixture->away_score) {
                    $map[$home] += $points['win'];
                } elseif ($fixture->home_score < $fixture->away_score) {
                    $map[$away] += $points['win'];
                } else {
                    $map[$home] += $points['draw'];
                    $map[$away] += $points['draw'];
                }
            });

        return collect($map);
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
     * @param  array{win:int,draw:int,goal:int}  $points
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

            // Which team won/drew (losers absent from the map).
            $teamResult = [];
            if ($fixture->home_score > $fixture->away_score) {
                $teamResult[$fixture->home_team_id] = 'win';
            } elseif ($fixture->home_score < $fixture->away_score) {
                $teamResult[$fixture->away_team_id] = 'win';
            } else {
                $teamResult[$fixture->home_team_id] = 'draw';
                $teamResult[$fixture->away_team_id] = 'draw';
            }

            $awards = [];

            // Team-based points.
            foreach ($enriched as $entry) {
                foreach ($entry['team_ids'] as $teamId) {
                    if (! isset($teamResult[$teamId])) {
                        continue;
                    }
                    $result = $teamResult[$teamId];
                    $awards[] = [
                        'entry_name' => $entry['entry_name'],
                        'points' => $result === 'win' ? $points['win'] : $points['draw'],
                        'reason' => ($teams[$teamId]->name ?? 'Team') . ' ' . $result,
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
                        'entry_name' => $entry['entry_name'],
                        'points' => $count * $points['goal'],
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
     * All remaining scheduled fixtures (soonest first), each annotated with the
     * entries that have a team — or a player — involved in the match.
     */
    protected function buildUpcoming(Collection $enriched, Collection $teams): Collection
    {
        $fixtures = WcFixture::query()
            ->where('status', 'scheduled')
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('match_datetime')
            ->get();

        return $fixtures->map(function (WcFixture $fixture) use ($enriched, $teams) {
            $fixtureTeamIds = array_values(array_filter([$fixture->home_team_id, $fixture->away_team_id]));

            $teamWatchers = [];
            $playerWatchers = [];

            foreach ($enriched as $entry) {
                foreach ($entry['team_ids'] as $teamId) {
                    if (in_array($teamId, $fixtureTeamIds)) {
                        $teamWatchers[] = [
                            'entry_name' => $entry['entry_name'],
                            'team_name' => $teams[$teamId]->name ?? '',
                        ];
                    }
                }

                foreach ($entry['players'] as $player) {
                    if ($player['team_id'] !== null && in_array($player['team_id'], $fixtureTeamIds)) {
                        $playerWatchers[] = [
                            'entry_name' => $entry['entry_name'],
                            'player_name' => $player['name'],
                            'team_name' => $player['team_name'],
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
