<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class PlayersController extends Controller
{
    public function index()
    {
        $players = DB::table('members')
            ->where('memberActive', 1)
            ->orderBy('memberNameLast', 'asc')
            ->orderBy('memberNameFirst', 'asc')
            ->get();

        $memberIDs = $players->pluck('memberID');

        $goalCounts = DB::table('scoring-actions')
            ->whereIn('memberID', $memberIDs)
            ->where('actionGoal', 1)
            ->where('actionActive', 1)
            ->select('memberID', DB::raw('count(*) as total'))
            ->groupBy('memberID')
            ->pluck('total', 'memberID');

        $assistCounts = DB::table('scoring-actions')
            ->whereIn('secondID', $memberIDs)
            ->where('actionGoal', 1)
            ->where('actionActive', 1)
            ->select('secondID', DB::raw('count(*) as total'))
            ->groupBy('secondID')
            ->pluck('total', 'secondID');

        $gameCounts = DB::table('members as m')
            ->join('results as r', 'r.resultMember', '=', 'm.memberKey')
            ->whereIn('m.memberID', $memberIDs)
            ->where('r.resultActive', 1)
            ->select('m.memberID', DB::raw('count(distinct r.resultGame) as total'))
            ->groupBy('m.memberID')
            ->pluck('total', 'memberID');

        $allAwards = DB::table('season-awards as sa')
            ->join('seasons as s', 'sa.seasonID', '=', 's.seasonID')
            ->where('sa.awardActive', 1)
            ->select('sa.awardPlayer1', 'sa.awardPlayer2', 'sa.awardPlayer3', 's.seasonName')
            ->get();

        $memberAwards = [];
        foreach ($allAwards as $award) {
            if ($award->awardPlayer1) $memberAwards[(int)$award->awardPlayer1][] = ['position' => 1, 'season' => $award->seasonName];
            if ($award->awardPlayer2) $memberAwards[(int)$award->awardPlayer2][] = ['position' => 2, 'season' => $award->seasonName];
            if ($award->awardPlayer3) $memberAwards[(int)$award->awardPlayer3][] = ['position' => 3, 'season' => $award->seasonName];
        }

        $players = $players->map(function ($player) use ($goalCounts, $assistCounts, $gameCounts, $memberAwards) {
            $awards = $memberAwards[(int)$player->memberID] ?? [];
            usort($awards, fn($a, $b) => $a['position'] - $b['position']);
            $player->goals   = $goalCounts[$player->memberID]   ?? 0;
            $player->assists = $assistCounts[$player->memberID] ?? 0;
            $player->games   = $gameCounts[$player->memberID]   ?? 0;
            $player->awards  = $awards;
            return $player;
        });

        return view('players', compact('players'));
    }

    public function show($memberSlug)
    {
        $member = DB::table('members')
            ->where('memberSlug', $memberSlug)
            ->where('memberActive', 1)
            ->firstOrFail();

        $goals = DB::table('scoring-actions')
            ->where('memberID', $member->memberID)
            ->where('actionGoal', 1)
            ->where('actionActive', 1)
            ->count();

        $assists = DB::table('scoring-actions')
            ->where('secondID', $member->memberID)
            ->where('actionGoal', 1)
            ->where('actionActive', 1)
            ->count();

        $gamesPlayed = DB::table('results')
            ->where('resultMember', $member->memberKey)
            ->where('resultActive', 1)
            ->count(DB::raw('distinct resultGame'));

        $dateRange = DB::table('results as r')
            ->join('games as g', 'g.gameKey', '=', 'r.resultGame')
            ->where('r.resultMember', $member->memberKey)
            ->where('r.resultActive', 1)
            ->selectRaw("
                MIN(CASE WHEN g.gameDate LIKE '%/%'
                    THEN STR_TO_DATE(g.gameDate, '%e/%c/%Y')
                    ELSE STR_TO_DATE(g.gameDate, '%Y-%m-%d') END) as firstPlayed,
                MAX(CASE WHEN g.gameDate LIKE '%/%'
                    THEN STR_TO_DATE(g.gameDate, '%e/%c/%Y')
                    ELSE STR_TO_DATE(g.gameDate, '%Y-%m-%d') END) as lastPlayed
            ")
            ->first();

        $firstPlayed = $dateRange?->firstPlayed
            ? \Carbon\Carbon::parse($dateRange->firstPlayed)->format('F Y')
            : null;
        $lastPlayed = $dateRange?->lastPlayed
            ? \Carbon\Carbon::parse($dateRange->lastPlayed)->format('F Y')
            : null;

        $goalsByTeam = DB::table('scoring-actions')
            ->where('memberID', $member->memberID)
            ->where('actionGoal', 1)
            ->where('actionActive', 1)
            ->whereIn('teamID', [1, 2, 3])
            ->select('teamID', DB::raw('count(*) as total'))
            ->groupBy('teamID')
            ->pluck('total', 'teamID');

        $goalsBySeason = DB::table('scoring-actions as a')
            ->join('games as g', 'a.gameID', '=', 'g.gameID')
            ->join('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
            ->where('a.memberID', $member->memberID)
            ->where('a.actionGoal', 1)
            ->where('a.actionActive', 1)
            ->where('g.gameVisible', 1)
            ->select('s.seasonID', DB::raw('count(*) as goals'))
            ->groupBy('s.seasonID')
            ->get();

        $assistsBySeason = DB::table('scoring-actions as a')
            ->join('games as g', 'a.gameID', '=', 'g.gameID')
            ->join('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
            ->where('a.secondID', $member->memberID)
            ->where('a.actionGoal', 1)
            ->where('a.actionActive', 1)
            ->where('g.gameVisible', 1)
            ->select('s.seasonID', DB::raw('count(*) as assists'))
            ->groupBy('s.seasonID')
            ->get();

        $gamesBySeason = DB::table('results as r')
            ->join('games as g', 'g.gameKey', '=', 'r.resultGame')
            ->join('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
            ->where('r.resultMember', $member->memberKey)
            ->where('r.resultActive', 1)
            ->where('g.gameVisible', 1)
            ->select('s.seasonID', DB::raw('count(distinct r.resultGame) as games'))
            ->groupBy('s.seasonID')
            ->get();

        $allSeasonIDs = collect()
            ->merge($goalsBySeason->pluck('seasonID'))
            ->merge($assistsBySeason->pluck('seasonID'))
            ->merge($gamesBySeason->pluck('seasonID'))
            ->unique();

        $goalsIndex   = $goalsBySeason->keyBy('seasonID');
        $assistsIndex = $assistsBySeason->keyBy('seasonID');
        $gamesIndex   = $gamesBySeason->keyBy('seasonID');

        $seasonsIndex = DB::table('seasons')
            ->whereIn('seasonID', $allSeasonIDs)
            ->get()
            ->keyBy('seasonID');

        $seasonBreakdown = $allSeasonIDs->map(function ($seasonID) use ($goalsIndex, $assistsIndex, $gamesIndex, $seasonsIndex) {
            return (object)[
                'seasonID'   => $seasonID,
                'seasonName' => $seasonsIndex[$seasonID]->seasonName ?? 'Unknown',
                'seasonLink' => $seasonsIndex[$seasonID]->seasonLink ?? '',
                'goals'      => $goalsIndex[$seasonID]->goals   ?? 0,
                'assists'    => $assistsIndex[$seasonID]->assists ?? 0,
                'games'      => $gamesIndex[$seasonID]->games    ?? 0,
            ];
        })->sortByDesc(function ($s) {
            return $s->seasonID;
        })->values();

        $awardHistory = DB::table('season-awards as a')
            ->join('seasons as s', 'a.seasonID', '=', 's.seasonID')
            ->where('a.awardActive', 1)
            ->where(function ($q) use ($member) {
                $q->where('a.awardPlayer1', $member->memberID)
                  ->orWhere('a.awardPlayer2', $member->memberID)
                  ->orWhere('a.awardPlayer3', $member->memberID);
            })
            ->select('a.*', 's.seasonName', 's.seasonLink')
            ->get()
            ->map(function ($award) use ($member) {
                $award->position = match(true) {
                    $award->awardPlayer1 == $member->memberID => 1,
                    $award->awardPlayer2 == $member->memberID => 2,
                    default => 3,
                };
                return $award;
            })
            ->sortBy('position')
            ->values();

        $recentActions = DB::table('scoring-actions as a')
            ->join('games as g', 'a.gameID', '=', 'g.gameID')
            ->join('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
            ->join('scoring as sc', 'a.scoringID', '=', 'sc.scoringID')
            ->leftJoin('members as m2', 'a.secondID', '=', 'm2.memberID')
            ->where('a.memberID', $member->memberID)
            ->where('a.actionActive', 1)
            ->where('g.gameVisible', 1)
            ->select(
                'a.actionID',
                'a.typeID',
                'a.teamID',
                'a.actionTime',
                's.seasonName',
                's.seasonLink',
                'g.gameRound',
                'g.gameYouTube',
                'g.gameYouTubeStart',
                'sc.scoringGame',
                'm2.memberNameFirst as assistFirst',
                'm2.memberNameLast as assistLast'
            )
            ->orderBy('a.actionID', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($action) {
                $action->youtubeURL = null;
                if ($action->gameYouTube && $action->gameYouTubeStart && $action->actionTime) {
                    preg_match('/(?:youtu\.be\/|v=)([^&\s]+)/', $action->gameYouTube, $matches);
                    if (!empty($matches[1])) {
                        $start    = strtotime($action->gameYouTubeStart);
                        $actionTs = strtotime($action->actionTime);
                        if ($start && $actionTs && $actionTs > $start) {
                            $action->youtubeURL = 'https://www.youtube.com/watch?v=' . $matches[1] . '&t=' . ($actionTs - $start) . 's';
                        }
                    }
                }
                return $action;
            });

        return view('players.show', compact(
            'member',
            'goals',
            'assists',
            'gamesPlayed',
            'firstPlayed',
            'lastPlayed',
            'goalsByTeam',
            'seasonBreakdown',
            'awardHistory',
            'recentActions'
        ));
    }
}
