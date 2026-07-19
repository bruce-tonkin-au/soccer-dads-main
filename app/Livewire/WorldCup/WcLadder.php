<?php

namespace App\Livewire\WorldCup;

use App\Models\WcEntry;
use App\Models\WcEntryTeam;
use App\Models\WcGoal;
use Illuminate\Support\Collection;

class WcLadder extends WcPage
{
    public function render()
    {
        $pointsKey = $this->pointsKey();
        $drawRun = WcEntryTeam::query()->exists();

        [$entries, $teams, $players, $memberNames] = $this->loadEntries();

        $teamPoints = $this->teamPointsMap($pointsKey);
        $goalCounts = WcGoal::query()
            ->where('is_own_goal', false)
            ->where('is_shootout', false)
            ->selectRaw('"playerID", count(*) as goals')
            ->groupBy('playerID')
            ->pluck('goals', 'playerID');

        // teamID => goals-for, one bulk query. Own goals ARE counted: wc_goals.teamID
        // already holds the benefiting team, so grouping by teamID gives each team's
        // true goal tally (matching the scoreline and teamPointsMap). Shootout goals
        // are excluded — they don't appear on the 90+ET scoreline. Drives the ⚽
        // count shown under each top-24 / bottom-24 team name.
        $teamGoalMap = WcGoal::query()
            ->where('is_shootout', false)
            ->selectRaw('"teamID", count(*) as goals')
            ->groupBy('teamID')
            ->pluck('goals', 'teamID');

        $enriched = $entries->map(fn (WcEntry $e) => $this->entryRow($e, $teams, $players, $teamPoints, $goalCounts, $pointsKey, $memberNames, $teamGoalMap));

        return view('livewire.world-cup.wc-ladder', [
            'pointsKey' => $pointsKey,
            'liveFixtures' => $this->liveFixtures(),
            'ladder' => $this->ladder($enriched, $drawRun),
        ])
            ->extends('layouts.app')
            ->section('content');
    }

    /**
     * Ranked ladder. Sorted by points once the draw has run (with positions /
     * medals), otherwise left in member-name order with no position.
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

        $sorted = $enriched
            ->sortByDesc('total_points')
            ->values();

        // Standard competition ranking ("1224"): rank = 1 + the number of
        // entries with STRICTLY more points. Tied entries share a rank (both
        // 27s => 2nd) and the next distinct score skips the consumed positions
        // (two 2nds consume ranks 2 & 3, so the next is 4th). Display order
        // within a tie is preserved by the stable sortByDesc above; only the
        // position number is de-duplicated.
        return $sorted->map(function (array $row) use ($sorted) {
            $row['position'] = 1 + $sorted
                ->where('total_points', '>', $row['total_points'])
                ->count();

            return $row;
        });
    }
}
