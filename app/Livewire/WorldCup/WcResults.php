<?php

namespace App\Livewire\WorldCup;

use App\Models\WcCard;
use App\Models\WcEntry;
use App\Models\WcFixture;
use App\Models\WcGoal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\WithPagination;

class WcResults extends WcPage
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

        return view('livewire.world-cup.wc-results', [
            'liveFixtures' => $this->liveFixtures(),
            'results' => $this->buildResults($enriched, $teams, $players, $pointsKey),
        ])
            ->extends('layouts.app')
            ->section('content');
    }

    /**
     * All completed fixtures (newest first) with scorers and a per-match
     * breakdown of which entries gained points and why.
     *
     * @param  array{team_goal:int,player_goal:int}  $points
     */
    protected function buildResults(Collection $enriched, Collection $teams, Collection $players, array $points): LengthAwarePaginator
    {
        $fixtures = WcFixture::query()
            ->where('status', 'completed')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->with(['homeTeam', 'awayTeam'])
            ->orderByDesc('match_datetime')
            ->paginate($this->perPage);

        $fixtureIds = $fixtures->pluck('fixtureID');

        $goalsByFixture = WcGoal::query()
            ->whereIn('fixtureID', $fixtureIds)
            ->with('player')
            ->get()
            ->groupBy('fixtureID');

        // Cards for the same fixtures, eager-loaded with player — no N+1.
        $cardsByFixture = WcCard::query()
            ->whereIn('fixtureID', $fixtureIds)
            ->with('player')
            ->get()
            ->groupBy('fixtureID');

        return $fixtures->through(function (WcFixture $fixture) use ($enriched, $teams, $players, $points, $goalsByFixture, $cardsByFixture) {
            $goals = $goalsByFixture[$fixture->fixtureID] ?? collect();
            $cards = $cardsByFixture[$fixture->fixtureID] ?? collect();

            $awards = [];

            // Team-goal points: per team credited with a goal this match. Own
            // goals ARE included — wc_goals.teamID holds the benefiting team.
            // Shootout goals are excluded (they don't award points).
            $byTeam = $goals->where('is_shootout', false)->groupBy('teamID');
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

            // Player-goal points: per scorer (non own goal, non shootout) in
            // this match.
            $byScorer = $goals
                ->where('is_own_goal', false)
                ->where('is_shootout', false)
                ->groupBy('playerID');
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
                'cards' => $this->cardLine($cards),
                'awards' => $awards,
            ];
        });
    }

    /**
     * Card line for a fixture, e.g. "🟨 Smith · 🟨 Jones · 🟥 Vidal". Yellows
     * first, then reds (a 2nd-yellow red shows as a red). Empty when no cards.
     */
    protected function cardLine(Collection $cards): string
    {
        $order = ['yellow' => 0, 'red' => 1];

        return $cards
            ->sortBy(fn (WcCard $c) => $order[$c->type] ?? 2)
            ->map(fn (WcCard $c) => ($c->type === 'red' ? '🟥' : '🟨') . ' ' . ($c->player?->name ?? 'Player'))
            ->implode(' · ');
    }
}
