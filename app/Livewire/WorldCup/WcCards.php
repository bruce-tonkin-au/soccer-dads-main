<?php

namespace App\Livewire\WorldCup;

use App\Models\WcCard;
use App\Models\WcEntry;
use App\Models\WcEntryTeam;
use App\Models\WcTeam;
use Illuminate\Support\Collection;

class WcCards extends WcPage
{
    public function render()
    {
        $cardPoints = $this->cardPointsKey();
        $drawRun = WcEntryTeam::query()->exists();

        return view('livewire.world-cup.wc-cards', [
            'cardPoints' => $cardPoints,
            'liveFixtures' => $this->liveFixtures(),
            'ladder' => $this->buildCardLadder($cardPoints, $drawRun),
        ])
            ->extends('layouts.app')
            ->section('content');
    }

    /**
     * Card ladder. Same shape as the goal ladder, but each entry's points are
     * driven by the yellow / red cards picked up by its two assigned teams.
     *
     * @param  array{yellow:int,red:int}  $cardPoints
     */
    protected function buildCardLadder(array $cardPoints, bool $drawRun): Collection
    {
        [$entries, $teams, $players, $memberNames] = $this->loadEntries();

        // teamID => ['yellow' => int, 'red' => int], one bulk query. Drives both
        // the per-team card points and the per-team counts shown under each team.
        $teamCardCounts = $this->teamCardCounts();
        $teamCardPoints = $teamCardCounts->map(
            fn (array $c) => $c['yellow'] * $cardPoints['yellow'] + $c['red'] * $cardPoints['red'],
        );
        [$playerYellow, $playerRed] = $this->playerCardCounts();

        $enriched = $entries->map(function (WcEntry $entry) use ($teams, $players, $teamCardCounts, $teamCardPoints, $playerYellow, $playerRed, $memberNames) {
            $topTeamId = $entry->entryTeams->firstWhere('tier', 1)?->teamID;
            $bottomTeamId = $entry->entryTeams->firstWhere('tier', 2)?->teamID;

            $teamIds = array_values(array_filter([$topTeamId, $bottomTeamId]));
            $cardPts = collect($teamIds)->sum(fn ($id) => $teamCardPoints[$id] ?? 0);

            $playerRows = $entry->entryPlayers
                ->sortBy('slot')
                ->map(function ($ep) use ($players, $playerYellow, $playerRed) {
                    $player = $players[$ep->playerID] ?? null;

                    return [
                        'playerID' => $ep->playerID,
                        'name' => $player?->name ?? '—',
                        'flag' => $player?->team?->flag,
                        'yellow_count' => (int) ($playerYellow[$ep->playerID] ?? 0),
                        'red_count' => (int) ($playerRed[$ep->playerID] ?? 0),
                    ];
                })
                ->values();

            return [
                'entryID' => $entry->entryID,
                'entry_name' => $entry->entry_name,
                'member_name' => $memberNames[$entry->memberID] ?? $entry->entry_name,
                'top_team' => $this->teamCardRow($teams[$topTeamId] ?? null, $topTeamId, $teamCardCounts),
                'bottom_team' => $this->teamCardRow($teams[$bottomTeamId] ?? null, $bottomTeamId, $teamCardCounts),
                'players' => $playerRows->all(),
                'card_points' => $cardPts,
            ];
        });

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
            ->sortByDesc('card_points')
            ->values()
            ->map(function (array $row, int $index) {
                $row['position'] = $index + 1;

                return $row;
            });
    }

    /**
     * Yellow / red card counts per team in a single grouped query. Yellow
     * excludes the booking that became a 2nd-yellow red; red includes 2nd
     * yellows. Returns teamID => ['yellow' => int, 'red' => int].
     */
    protected function teamCardCounts(): Collection
    {
        return WcCard::query()
            ->selectRaw('"teamID",
                count(*) filter (where type = \'yellow\' and is_second_yellow = false) as yellow,
                count(*) filter (where type = \'red\') as red')
            ->groupBy('teamID')
            ->get()
            ->keyBy('teamID')
            ->map(fn ($r) => ['yellow' => (int) $r->yellow, 'red' => (int) $r->red]);
    }

    /**
     * A team row (flag / name / group) augmented with its yellow & red card
     * counts for display under the team name. Null when the entry has no team
     * for that tier (e.g. before the draw).
     */
    protected function teamCardRow(?WcTeam $team, ?int $teamId, Collection $counts): ?array
    {
        $row = $this->teamRow($team);
        if ($row === null) {
            return null;
        }

        $c = $counts[$teamId] ?? ['yellow' => 0, 'red' => 0];
        $row['yellow_count'] = $c['yellow'];
        $row['red_count'] = $c['red'];

        return $row;
    }

    /**
     * Per-player yellow / red counts for badge display (red includes 2nd
     * yellows; yellow excludes the booking that became a 2nd-yellow red).
     *
     * @return array{0:Collection,1:Collection}
     */
    protected function playerCardCounts(): array
    {
        $yellow = WcCard::query()
            ->where('type', 'yellow')
            ->where('is_second_yellow', false)
            ->selectRaw('"playerID", count(*) as c')
            ->groupBy('playerID')
            ->pluck('c', 'playerID');

        $red = WcCard::query()
            ->where('type', 'red')
            ->selectRaw('"playerID", count(*) as c')
            ->groupBy('playerID')
            ->pluck('c', 'playerID');

        return [$yellow, $red];
    }
}
