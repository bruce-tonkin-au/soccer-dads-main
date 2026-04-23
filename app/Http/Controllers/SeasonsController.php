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
        ->get();

    // Get all games at once
    $allGames = DB::table('games')
        ->where('gameVisible', 1)
        ->get()
        ->groupBy('gameSeason');

    // Get all game IDs
    $allGameIDs = DB::table('games')
        ->where('gameVisible', 1)
        ->pluck('gameID');

    // Get all goals at once grouped by gameID
    $allGoals = DB::table('scoring-actions as a')
        ->join('scoring as s', 'a.scoringID', '=', 's.scoringID')
        ->whereIn('s.gameID', $allGameIDs)
        ->where('a.actionGoal', 1)
        ->where('a.actionActive', 1)
        ->select('s.gameID')
        ->get()
        ->groupBy('gameID');

    // Get all awards at once
    $allAwards = DB::table('season-awards')
        ->where('awardActive', 1)
        ->get()
        ->keyBy('seasonID');

    // Get all members needed for awards
    $awardMemberIDs = $allAwards->flatMap(fn($a) => [$a->awardPlayer1, $a->awardPlayer2, $a->awardPlayer3])
        ->filter()
        ->unique();

    $awardMembers = DB::table('members')
        ->whereIn('memberID', $awardMemberIDs)
        ->get()
        ->keyBy('memberID');

    $seasons = $seasons->map(function($season) use ($allGames, $allGoals, $allAwards, $awardMembers) {
        $games = $allGames[$season->seasonKey] ?? collect();
        $gameIDs = $games->pluck('gameID');
        $sessions = $games->count();

        $goals = $gameIDs->sum(fn($id) => isset($allGoals[$id]) ? $allGoals[$id]->count() : 0);

        $award = $allAwards[$season->seasonID] ?? null;

        $winner = $second = $third = null;

        if ($award) {
            if ($award->awardPlayer1 && isset($awardMembers[$award->awardPlayer1])) {
                $m = $awardMembers[$award->awardPlayer1];
                $winner = $m->memberNameFirst . ' ' . $m->memberNameLast;
            }
            if ($award->awardPlayer2 && isset($awardMembers[$award->awardPlayer2])) {
                $m = $awardMembers[$award->awardPlayer2];
                $second = $m->memberNameFirst . ' ' . $m->memberNameLast;
            }
            if ($award->awardPlayer3 && isset($awardMembers[$award->awardPlayer3])) {
                $m = $awardMembers[$award->awardPlayer3];
                $third = $m->memberNameFirst . ' ' . $m->memberNameLast;
            }
        }

        return (object)[
            'seasonKey'  => $season->seasonKey,
            'seasonName' => $season->seasonName,
            'sessions'   => $sessions,
            'goals'      => $goals,
            'winner'     => $winner,
            'second'     => $second,
            'third'      => $third,
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
        $nights = $games->map(function ($game) {
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
                if ($homeGoals > $awayGoals) {
                    $teamGoals[$row->scoringTeamHome] = ($teamGoals[$row->scoringTeamHome] ?? 0) + 2;
                } elseif ($awayGoals > $homeGoals) {
                    $teamGoals[$row->scoringTeamAway] = ($teamGoals[$row->scoringTeamAway] ?? 0) + 2;
                } else {
                    $teamGoals[$row->scoringTeamHome] = ($teamGoals[$row->scoringTeamHome] ?? 0) + 1;
                    $teamGoals[$row->scoringTeamAway] = ($teamGoals[$row->scoringTeamAway] ?? 0) + 1;
                }
            }

            arsort($teamGoals);
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

        // All time goals
        $allTimeActions = DB::table('scoring-actions')
            ->where('actionGoal', 1)
            ->where('actionActive', 1)
            ->whereNotNull('memberID')
            ->get();

        $allTimeGoals = $allTimeActions->groupBy('memberID')
            ->map(fn($g) => $g->count());

        $allTimeAssists = $allTimeActions->whereNotNull('secondID')
            ->groupBy('secondID')
            ->map(fn($g) => $g->count());

        // YTD goals (this calendar year)
        $currentYear = \Carbon\Carbon::parse($game->gameDate)->year;

        $allGames = DB::table('games')
            ->where('gameVisible', 1)
            ->get();

        $ytdGameIDs = $allGames->filter(function ($g) use ($currentYear) {
            try {
                $parts = explode('/', $g->gameDate);
                if (count($parts) === 3) {
                    return (int)$parts[2] === $currentYear;
                }
                return \Carbon\Carbon::parse($g->gameDate)->year === $currentYear;
            } catch (\Exception $e) {
                return false;
            }
        })->pluck('gameID');

        $ytdActions = DB::table('scoring-actions as a')
            ->join('scoring as s', 'a.scoringID', '=', 's.scoringID')
            ->whereIn('s.gameID', $ytdGameIDs)
            ->where('a.actionGoal', 1)
            ->where('a.actionActive', 1)
            ->get();

        $ytdGoals = $ytdActions->groupBy('memberID')->map(fn($g) => $g->count());
        $ytdAssists = $ytdActions->whereNotNull('secondID')->groupBy('secondID')->map(fn($g) => $g->count());

        // Team results for the night
$results = $scoringRows->map(function ($row) use ($actions, $teams) {
    $homeGoals = $actions->where('scoringID', $row->scoringID)->where('teamID', $row->scoringTeamHome)->where('actionGoal', 1)->count();
    $awayGoals = $actions->where('scoringID', $row->scoringID)->where('teamID', $row->scoringTeamAway)->where('actionGoal', 1)->count();
    return (object)[
        'scoringRound' => $row->scoringRound,
        'scoringGame'  => $row->scoringGame,
        'homeTeam'     => array_merge($teams[$row->scoringTeamHome] ?? ['name' => 'Unknown', 'color' => '#aaa'], ['id' => $row->scoringTeamHome]),
        'awayTeam'     => array_merge($teams[$row->scoringTeamAway] ?? ['name' => 'Unknown', 'color' => '#aaa'], ['id' => $row->scoringTeamAway]),
        'homeGoals'    => $homeGoals,
        'awayGoals'    => $awayGoals,
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
            'season',
            'game',
            'teams',
            'actions',
            'results',
            'nightGoals',
            'seasonGoals',
            'seasonAssists',
            'ytdGoals',
            'ytdAssists',
            'allTimeGoals',
            'allTimeAssists',
            'youtubeID',
            'youtubeStart'
        ));
    }
}
