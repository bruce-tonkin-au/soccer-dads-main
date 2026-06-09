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
     * Top 2 sweepstake entry names by calculated points (team wins/draws from
     * wc_fixtures + player goals from wc_goals), or an empty array if the draw
     * hasn't been made. Lightweight — a few bulk queries, no model hydration.
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
            ->whereIn('key', ['points_team_win', 'points_team_draw', 'points_player_goal'])
            ->pluck('value', 'key');
        $win = (int) ($settings['points_team_win'] ?? 3);
        $draw = (int) ($settings['points_team_draw'] ?? 1);
        $goal = (int) ($settings['points_player_goal'] ?? 2);

        // Points earned by each team across completed fixtures.
        $teamPoints = [];
        DB::table('wc_fixtures')
            ->where('status', 'completed')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->get(['home_team_id', 'away_team_id', 'home_score', 'away_score'])
            ->each(function ($f) use (&$teamPoints, $win, $draw) {
                $teamPoints[$f->home_team_id] ??= 0;
                $teamPoints[$f->away_team_id] ??= 0;
                if ($f->home_score > $f->away_score) {
                    $teamPoints[$f->home_team_id] += $win;
                } elseif ($f->home_score < $f->away_score) {
                    $teamPoints[$f->away_team_id] += $win;
                } else {
                    $teamPoints[$f->home_team_id] += $draw;
                    $teamPoints[$f->away_team_id] += $draw;
                }
            });

        // Goals scored per player (excluding own goals).
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
            ->map(function ($entry) use ($teamsByEntry, $playersByEntry, $teamPoints, $goalCounts, $goal) {
                $points = 0;
                foreach ($teamsByEntry[$entry->entryID] ?? [] as $t) {
                    $points += $teamPoints[$t->teamID] ?? 0;
                }
                foreach ($playersByEntry[$entry->entryID] ?? [] as $p) {
                    $points += ($goalCounts[$p->playerID] ?? 0) * $goal;
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