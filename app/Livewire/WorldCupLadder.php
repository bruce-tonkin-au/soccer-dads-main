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
    public function mount(): void
    {
        // Nothing heavy — all data is loaded in render().
    }

    public function render()
    {
        $pointsKey = $this->pointsKey();
        $drawRun = WcEntryTeam::query()->exists();

        return view('livewire.world-cup-ladder', [
            'ladder' => $this->buildLadder($pointsKey, $drawRun),
            'recentResults' => $this->recentResults(),
            'upcomingFixtures' => $this->upcomingFixtures(),
            'drawRun' => $drawRun,
            'pointsKey' => $pointsKey,
        ])
            ->extends('layouts.app')
            ->section('content');
    }

    /**
     * Scoring values from wc_settings.
     *
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
     * The ranked sweepstake ladder. Empty until the draw has been made.
     * Built from a handful of bulk queries — no per-entry lookups.
     *
     * @param  array{win:int,draw:int,goal:int}  $points
     */
    protected function buildLadder(array $points, bool $drawRun): Collection
    {
        if (! $drawRun) {
            return collect();
        }

        $entries = WcEntry::query()
            ->where('draw_completed', true)
            ->with(['entryTeams', 'entryPlayers'])
            ->get();

        if ($entries->isEmpty()) {
            return collect();
        }

        // Points earned by every team across completed fixtures (one pass).
        $teamPoints = $this->teamPointsMap($points);

        // Goals scored per player (excluding own goals), one grouped query.
        $goalCounts = WcGoal::query()
            ->where('is_own_goal', false)
            ->selectRaw('"playerID", count(*) as goals')
            ->groupBy('playerID')
            ->pluck('goals', 'playerID');

        // Bulk-load the teams and players referenced by all entries.
        $teamIds = $entries->flatMap(fn (WcEntry $e) => $e->entryTeams->pluck('teamID'))->unique();
        $playerIds = $entries->flatMap(fn (WcEntry $e) => $e->entryPlayers->pluck('playerID'))->unique();

        $teams = WcTeam::whereIn('teamID', $teamIds)->get()->keyBy('teamID');
        $players = WcPlayer::with('team')->whereIn('playerID', $playerIds)->get()->keyBy('playerID');

        $rows = $entries->map(function (WcEntry $entry) use ($points, $teamPoints, $goalCounts, $teams, $players) {
            $topTeamId = $entry->entryTeams->firstWhere('tier', 1)?->teamID;
            $bottomTeamId = $entry->entryTeams->firstWhere('tier', 2)?->teamID;

            $teamPts = ($teamPoints[$topTeamId] ?? 0) + ($teamPoints[$bottomTeamId] ?? 0);

            $playerRows = $entry->entryPlayers
                ->sortBy('slot')
                ->map(function ($ep) use ($players, $goalCounts) {
                    $player = $players[$ep->playerID] ?? null;

                    return [
                        'name' => $player?->name ?? '—',
                        'team_code' => $player?->team?->code,
                        'flag' => $player?->team?->flag,
                        'goal_count' => (int) ($goalCounts[$ep->playerID] ?? 0),
                    ];
                })
                ->values();

            $playerGoals = $playerRows->sum('goal_count');
            $playerPoints = $playerGoals * $points['goal'];

            return [
                'entry_name' => $entry->entry_name,
                'team_points' => $teamPts,
                'player_points' => $playerPoints,
                'total_points' => $teamPts + $playerPoints,
                'top_team' => $this->teamRow($teams[$topTeamId] ?? null),
                'bottom_team' => $this->teamRow($teams[$bottomTeamId] ?? null),
                'players' => $playerRows->all(),
            ];
        });

        // Rank descending; PHP's stable sort keeps ties in entry order.
        return $rows
            ->sortByDesc('total_points')
            ->values()
            ->map(function (array $row, int $index) {
                $row['position'] = $index + 1;

                return $row;
            });
    }

    /**
     * Map of teamID => points earned across all completed fixtures.
     *
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

    /**
     * @return array{flag:?string,name:string,code:?string,group_letter:?string}|null
     */
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
     * Last 5 completed fixtures with scorers.
     */
    protected function recentResults(): Collection
    {
        $fixtures = WcFixture::query()
            ->where('status', 'completed')
            ->with(['homeTeam', 'awayTeam'])
            ->orderByDesc('match_datetime')
            ->limit(5)
            ->get();

        if ($fixtures->isEmpty()) {
            return collect();
        }

        $scorers = WcGoal::query()
            ->where('is_own_goal', false)
            ->whereIn('fixtureID', $fixtures->pluck('fixtureID'))
            ->with('player')
            ->get()
            ->groupBy('fixtureID');

        return $fixtures->map(function (WcFixture $fixture) use ($scorers) {
            $names = ($scorers[$fixture->fixtureID] ?? collect())
                ->map(fn (WcGoal $goal) => $goal->player?->name)
                ->filter()
                ->implode(', ');

            return [
                'date' => $fixture->match_datetime,
                'home_flag' => $fixture->homeTeam?->flag,
                'home_name' => $fixture->homeTeam?->name ?? $fixture->home_placeholder,
                'away_flag' => $fixture->awayTeam?->flag,
                'away_name' => $fixture->awayTeam?->name ?? $fixture->away_placeholder,
                'home_score' => $fixture->home_score,
                'away_score' => $fixture->away_score,
                'scorers' => $names,
            ];
        });
    }

    /**
     * Next 6 scheduled fixtures.
     */
    protected function upcomingFixtures(): Collection
    {
        return WcFixture::query()
            ->where('status', 'scheduled')
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('match_datetime')
            ->limit(6)
            ->get()
            ->map(fn (WcFixture $fixture) => [
                'datetime' => $fixture->match_datetime,
                'group_letter' => $fixture->group_letter,
                'home_flag' => $fixture->homeTeam?->flag,
                'home_name' => $fixture->homeTeam?->name ?? $fixture->home_placeholder ?? 'TBD',
                'away_flag' => $fixture->awayTeam?->flag,
                'away_name' => $fixture->awayTeam?->name ?? $fixture->away_placeholder ?? 'TBD',
            ]);
    }
}
