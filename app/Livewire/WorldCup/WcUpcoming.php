<?php

namespace App\Livewire\WorldCup;

use App\Models\WcEntry;
use App\Models\WcFixture;
use App\Models\WcGoal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\WithPagination;

class WcUpcoming extends WcPage
{
    use WithPagination;

    protected int $perPage = 10;

    public function render()
    {
        $pointsKey = $this->pointsKey();

        [$entries, $teams, $players, $memberNames] = $this->loadEntries();

        $teamPoints = $this->teamPointsMap($pointsKey);
        $goalCounts = WcGoal::query()
            ->where('is_own_goal', false)
            ->where('is_shootout', false)
            ->selectRaw('"playerID", count(*) as goals')
            ->groupBy('playerID')
            ->pluck('goals', 'playerID');

        $enriched = $entries->map(fn (WcEntry $e) => $this->entryRow($e, $teams, $players, $teamPoints, $goalCounts, $pointsKey, $memberNames));

        return view('livewire.world-cup.wc-upcoming', [
            'liveFixtures' => $this->liveFixtures(),
            'upcoming' => $this->buildUpcoming($enriched, $teams),
        ])
            ->extends('layouts.app')
            ->section('content');
    }

    /**
     * Next scheduled fixtures (soonest first), each annotated with the entries
     * that have a team — or a player — involved in the match.
     */
    protected function buildUpcoming(Collection $enriched, Collection $teams): LengthAwarePaginator
    {
        $fixtures = WcFixture::query()
            ->where('status', 'scheduled')
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('match_datetime')
            ->paginate($this->perPage);

        return $fixtures->through(function (WcFixture $fixture) use ($enriched, $teams) {
            $fixtureTeamIds = array_values(array_filter([$fixture->home_team_id, $fixture->away_team_id]));

            $teamWatchers = [];
            $playerWatchers = [];

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
