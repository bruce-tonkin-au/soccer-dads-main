<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'seasons' => DB::table('seasons')->where('seasonVisible', 1)->count(),
            'sessions' => DB::table('games')->where('gameVisible', 1)->count(),
            'games'   => DB::table('scoring')->where('scoringActive', 1)->count(),
            'goals'   => DB::table('scoring-actions')->where('actionGoal', 1)->where('actionActive', 1)->whereNotIn('gameID', fn($q) => $q->select('gameID')->from('games')->where('is_test', true))->count(),
            'players' => DB::table('members')->count(),
        ];

        $nextGame = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->whereRaw('g."gameDate" >= CURRENT_DATE')
            ->orderByRaw('g."gameDate" ASC')
            ->select('g.*', 's.seasonName', 's.seasonLink')
            ->first();

        $wcLeaders = $this->worldCupLeaders();

        return view('home', compact('stats', 'nextGame', 'wcLeaders'));
    }

    /**
     * Top 2 sweepstake entry names by calculated points (team goals + player
     * goals from wc_goals), or an empty array if the draw hasn't been made.
     * Lightweight — a few bulk queries, no model hydration.
     *
     * @return array<int, string>
     */
    private function worldCupLeaders(): array
    {
        // Draw not run yet.
        if (! DB::table('wc_entry_teams')->exists()) {
            return [];
        }

        $settings = DB::table('wc_settings')
            ->whereIn('key', ['points_team_goal', 'points_player_goal'])
            ->pluck('value', 'key');
        $teamGoalPts = (int) ($settings['points_team_goal'] ?? 1);
        $playerGoalPts = (int) ($settings['points_player_goal'] ?? 1);

        // Goals scored per team and per player (excluding own goals).
        $teamGoals = DB::table('wc_goals')
            ->where('is_own_goal', false)
            ->groupBy('teamID')
            ->selectRaw('"teamID", count(*) as goals')
            ->pluck('goals', 'teamID');

        $goalCounts = DB::table('wc_goals')
            ->where('is_own_goal', false)
            ->groupBy('playerID')
            ->selectRaw('"playerID", count(*) as goals')
            ->pluck('goals', 'playerID');

        $entries = DB::table('wc_entries')->where('draw_completed', true)->get(['entryID', 'entry_name']);
        if ($entries->isEmpty()) {
            return [];
        }

        $entryIds = $entries->pluck('entryID');
        $teamsByEntry = DB::table('wc_entry_teams')->whereIn('entryID', $entryIds)->get(['entryID', 'teamID'])->groupBy('entryID');
        $playersByEntry = DB::table('wc_entry_players')->whereIn('entryID', $entryIds)->get(['entryID', 'playerID'])->groupBy('entryID');

        return $entries
            ->map(function ($entry) use ($teamsByEntry, $playersByEntry, $teamGoals, $goalCounts, $teamGoalPts, $playerGoalPts) {
                $points = 0;
                foreach ($teamsByEntry[$entry->entryID] ?? [] as $t) {
                    $points += ($teamGoals[$t->teamID] ?? 0) * $teamGoalPts;
                }
                foreach ($playersByEntry[$entry->entryID] ?? [] as $p) {
                    $points += ($goalCounts[$p->playerID] ?? 0) * $playerGoalPts;
                }

                return ['name' => $entry->entry_name, 'points' => $points];
            })
            ->sortByDesc('points')
            ->take(2)
            ->pluck('name')
            ->values()
            ->all();
    }
}