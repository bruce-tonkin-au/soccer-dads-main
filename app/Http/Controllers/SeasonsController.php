<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SeasonsController extends Controller
{
    public function index()
    {
        $seasons = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->get()
            ->map(function($season) {
                $games = DB::table('games')
                    ->where('gameSeason', $season->seasonKey)
                    ->where('gameVisible', 1)
                    ->get();

                $gameIDs = $games->pluck('gameID');

                $goals = DB::table('scoring-actions as a')
                    ->join('scoring as s', 'a.scoringID', '=', 's.scoringID')
                    ->whereIn('s.gameID', $gameIDs)
                    ->where('a.actionGoal', 1)
                    ->where('a.actionActive', 1)
                    ->count();

                $nights = $games->count();

                return (object)[
                    'seasonKey'  => $season->seasonKey,
                    'seasonName' => $season->seasonName,
                    'nights'     => $nights,
                    'goals'      => $goals,
                ];
            });

        return view('seasons.index', compact('seasons'));
    }

    public function show($seasonKey)
    {
        $season = DB::table('seasons')
            ->where('seasonKey', $seasonKey)
            ->where('seasonVisible', 1)
            ->firstOrFail();

        $games = DB::table('games')
            ->where('gameSeason', $seasonKey)
            ->where('gameVisible', 1)
            ->orderBy('gameRound', 'asc')
            ->get();

        $gameIDs = $games->pluck('gameID');

        $allActions = DB::table('scoring-actions as a')
            ->join('scoring as s', 'a.scoringID', '=', 's.scoringID')
            ->whereIn('s.gameID', $gameIDs)
            ->where('a.actionGoal', 1)
            ->where('a.actionActive', 1)
            ->get();

        $totalGoals = $allActions->count();

        $topScorer = DB::table('scoring-actions as a')
            ->join('scoring as s', 'a.scoringID', '=', 's.scoringID')
            ->join('members as m', 'a.memberID', '=', 'm.memberID')
            ->whereIn('s.gameID', $gameIDs)
            ->where('a.actionGoal', 1)
            ->where('a.actionActive', 1)
            ->whereNotNull('a.memberID')
            ->select('m.memberNameFirst', 'm.memberNameLast', DB::raw('count(*) as goals'))
            ->groupBy('a.memberID', 'm.memberNameFirst', 'm.memberNameLast')
            ->orderByDesc('goals')
            ->first();

        // Build nights with results
        $nights = $games->map(function($game) {
            $scoringRows = DB::table('scoring')
                ->where('gameID', $game->gameID)
                ->where('scoringActive', 1)
                ->get();

            $scoringIDs = $scoringRows->pluck('scoringID');

            $actions = DB::table('scoring-actions')
                ->whereIn('scoringID', $scoringIDs)
                ->where('actionGoal', 1)
                ->where('actionActive', 1)
                ->get();

            $teams = [
                1 => ['name' => 'Orange', 'color' => '#e68a46'],
                2 => ['name' => 'Green',  'color' => '#7bba56'],
                3 => ['name' => 'Blue',   'color' => '#458bc8'],
            ];

            $teamGoals = [];
            foreach ($scoringRows as $row) {
                $homeGoals = $actions->where('scoringID', $row->scoringID)->where('teamID', $row->scoringTeamHome)->count();
                $awayGoals = $actions->where('scoringID', $row->scoringID)->where('teamID', $row->scoringTeamAway)->count();
                $teamGoals[$row->scoringTeamHome] = ($teamGoals[$row->scoringTeamHome] ?? 0) + $homeGoals;
                $teamGoals[$row->scoringTeamAway] = ($teamGoals[$row->scoringTeamAway] ?? 0) + $awayGoals;
            }

            $hasResults = $actions->count() > 0;

            return (object)[
                'gameID'    => $game->gameID,
                'gameRound' => $game->gameRound,
                'gameDate'  => $game->gameDate,
                'teamGoals' => $teamGoals,
                'teams'     => $teams,
                'hasResults' => $hasResults,
            ];
        });

        return view('seasons.show', compact('season', 'nights', 'totalGoals', 'topScorer'));
    }

    public function night($seasonKey, $gameRound)
    {
        $season = DB::table('seasons')
            ->where('seasonKey', $seasonKey)
            ->where('seasonVisible', 1)
            ->firstOrFail();

        $game = DB::table('games')
            ->where('gameSeason', $seasonKey)
            ->where('gameRound', $gameRound)
            ->where('gameVisible', 1)
            ->firstOrFail();

        $teams = [
            1 => ['name' => 'Orange', 'color' => '#e68a46'],
            2 => ['name' => 'Green',  'color' => '#7bba56'],
            3 => ['name' => 'Blue',   'color' => '#458bc8'],
        ];

        $scoringRows = DB::table('scoring')
            ->where('gameID', $game->gameID)
            ->where('scoringActive', 1)
            ->orderBy('scoringRound')
            ->orderBy('scoringGame')
            ->get();

        $scoringIDs = $scoringRows->pluck('scoringID');

        $actions = DB::table('scoring-actions as a')
            ->join('scoring as s', 'a.scoringID', '=', 's.scoringID')
            ->leftJoin('members as m', 'a.memberID', '=', 'm.memberID')
            ->leftJoin('members as m2', 'a.secondID', '=', 'm2.memberID')
            ->whereIn('a.scoringID', $scoringIDs)
            ->where('a.actionActive', 1)
            ->orderBy('a.actionTime', 'asc')
            ->select(
                'a.*',
                's.scoringRound',
                's.scoringGame',
                's.scoringTeamHome',
                's.scoringTeamAway',
                'm.memberNameFirst as scorerFirst',
                'm.memberNameLast as scorerLast',
                'm2.memberNameFirst as assisterFirst',
                'm2.memberNameLast as assisterLast'
            )
            ->get();

        // Goals for this night per player
        $nightGoals = $actions->where('actionGoal', 1)
            ->groupBy('memberID')
            ->map(fn($g) => $g->count());

        // Goals for season YTD (up to and including this night)
        $previousGames = DB::table('games')
            ->where('gameSeason', $seasonKey)
            ->where('gameRound', '<=', $gameRound)
            ->where('gameVisible', 1)
            ->pluck('gameID');

        $seasonActions = DB::table('scoring-actions as a')
            ->join('scoring as s', 'a.scoringID', '=', 's.scoringID')
            ->whereIn('s.gameID', $previousGames)
            ->where('a.actionGoal', 1)
            ->where('a.actionActive', 1)
            ->get();

        $seasonGoals = $seasonActions->groupBy('memberID')
            ->map(fn($g) => $g->count());

        // Season assists YTD
        $seasonAssists = $seasonActions->whereNotNull('secondID')
            ->groupBy('secondID')
            ->map(fn($g) => $g->count());

        // Team results for the night
        $results = $scoringRows->map(function($row) use ($actions, $teams) {
            $homeGoals = $actions->where('scoringID', $row->scoringID)->where('teamID', $row->scoringTeamHome)->where('actionGoal', 1)->count();
            $awayGoals = $actions->where('scoringID', $row->scoringID)->where('teamID', $row->scoringTeamAway)->where('actionGoal', 1)->count();
            return (object)[
                'scoringRound'    => $row->scoringRound,
                'scoringGame'     => $row->scoringGame,
                'homeTeam'        => $teams[$row->scoringTeamHome],
                'awayTeam'        => $teams[$row->scoringTeamAway],
                'homeGoals'       => $homeGoals,
                'awayGoals'       => $awayGoals,
            ];
        });

        // YouTube info
        $youtubeID = null;
        if ($game->gameYouTube) {
            preg_match('/(?:youtu\.be\/|v=)([^&\s]+)/', $game->gameYouTube, $matches);
            $youtubeID = $matches[1] ?? null;
        }

        $youtubeStart = $game->gameYouTubeStart ?? null;

        return view('seasons.night', compact(
            'season', 'game', 'teams', 'actions', 'results',
            'nightGoals', 'seasonGoals', 'seasonAssists',
            'youtubeID', 'youtubeStart'
        ));
    }
}