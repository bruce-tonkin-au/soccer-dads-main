<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Season ladder + average-progression series, read from results only.
 * Extracted verbatim from AdminController so the legacy /admin games screen
 * and the /manage Season Games page share one implementation and cannot drift.
 *
 * results: one row per player per game; resultPoints is the 3/2/1 (3=win,
 * 2=mid, 1=low). Returns the sorted ladder (each row flagged ->eligible),
 * the live eligibility threshold, the pre-round average games-played, and
 * the Chart.js labels/series (title-eligible players only).
 */
class SeasonLadderService
{
    public function buildSeasonLadder($seasonID, $games)
    {
        $results = DB::table('results as r')
            ->join('members as m', 'r.resultMemberID', '=', 'm.memberID')
            ->join('games as g', 'r.resultGameID', '=', 'g.gameID')
            ->where('r.resultSeasonID', $seasonID)
            ->where('r.resultActive', 1)
            ->where('g.gameVisible', 1)      // exclude hidden/future placeholder rounds (e.g. Round 11)
            ->where('g.is_test', false)      // exclude test games
            ->select('r.resultMemberID', 'r.resultGameID', 'r.resultTeamID', 'r.resultPoints',
                     'm.memberNameFirst', 'm.memberNameLast')
            ->get();

        // Season team-points (the 0–42 night tally shown on the public season
        // page) per player. Not stored — recomputed from scoring + scoring-actions.
        $teamPointsByMember = $this->seasonTeamPointsByMember($games, $results);

        // Per-player aggregates: games played, total points, average, team points.
        $ladder = $results->groupBy('resultMemberID')->map(function ($rows) use ($teamPointsByMember) {
            $first       = $rows->first();
            $memberID    = (int) $first->resultMemberID;
            $gamesPlayed = $rows->pluck('resultGameID')->unique()->count();
            $totalPoints = (int) $rows->sum('resultPoints');
            return (object) [
                'memberID'    => $memberID,
                'name'        => trim($first->memberNameFirst . ' ' . $first->memberNameLast),
                'gamesPlayed' => $gamesPlayed,
                'totalPoints' => $totalPoints,
                'average'     => $gamesPlayed > 0 ? $totalPoints / $gamesPlayed : 0,
                'teamPoints'  => $teamPointsByMember[$memberID] ?? 0,
            ];
        })->values();

        // Eligibility threshold: ceil of the average games-played across all
        // listed players (everyone here has >= 1 game). Recomputed live.
        $playerCount    = $ladder->count();
        $avgGamesPlayed = $playerCount > 0 ? $ladder->sum('gamesPlayed') / $playerCount : 0;
        $threshold      = (int) ceil($avgGamesPlayed);

        // Flag eligibility, then sort: all eligible players first, then all
        // ineligible. Within each group: average DESC, season team-points DESC
        // (tiebreak), then games played DESC.
        $ladder = $ladder->map(function ($p) use ($threshold) {
            $p->eligible = $p->gamesPlayed >= $threshold;
            return $p;
        })->sort(function ($a, $b) {
            return [$b->eligible ? 1 : 0, $b->average, $b->teamPoints, $b->gamesPlayed]
               <=> [$a->eligible ? 1 : 0, $a->average, $a->teamPoints, $a->gamesPlayed];
        })->values();

        // Rounds that actually have results, in round order, for the X axis.
        $roundByGameId = $games->pluck('gameRound', 'gameID');
        $playedGameIds = $games->pluck('gameID')
            ->filter(fn ($id) => $results->contains('resultGameID', $id))
            ->values();
        $chartLabels = $playedGameIds->map(fn ($id) => 'R' . $roundByGameId[$id])->all();

        // Per member, per game points, for the cumulative-average series.
        $pointsByMemberGame = [];
        foreach ($results as $row) {
            $pointsByMemberGame[(int) $row->resultMemberID][(int) $row->resultGameID] = (int) $row->resultPoints;
        }

        // One line per title-eligible player: cumulative average (cumulative
        // points / cumulative games) after each played round; null before their
        // first game so the line only starts once they've played.
        $chartSeries = $ladder->filter(fn ($p) => $p->eligible)->map(function ($p) use ($playedGameIds, $pointsByMemberGame) {
            $cumPoints = 0;
            $cumGames  = 0;
            $data      = [];
            foreach ($playedGameIds as $gid) {
                $gid = (int) $gid;
                if (isset($pointsByMemberGame[$p->memberID][$gid])) {
                    $cumPoints += $pointsByMemberGame[$p->memberID][$gid];
                    $cumGames++;
                }
                $data[] = $cumGames > 0 ? round($cumPoints / $cumGames, 2) : null;
            }
            return ['name' => $p->name, 'data' => $data];
        })->values()->all();

        return compact('ladder', 'threshold', 'avgGamesPlayed', 'chartLabels', 'chartSeries');
    }

    /**
     * Sum, per player, of the match-point tally of the team they were on each
     * night they played — the same 0–42-scale number shown on the public season
     * page. Not stored; recomputed from scoring + scoring-actions with the same
     * win(+2)/draw(+1)/skip logic as SeasonsController::show() so the numbers
     * match. Loads the season's scoring + actions once and groups in PHP.
     *
     * @return array<int,int> memberID => season team-points total
     */
    private function seasonTeamPointsByMember($games, $results)
    {
        $scoringRows = DB::table('scoring')
            ->whereIn('gameID', $games->pluck('gameID'))
            ->where('scoringActive', 1)
            ->get();

        if ($scoringRows->isEmpty()) {
            return [];
        }

        $goalActions = DB::table('scoring-actions')
            ->whereIn('scoringID', $scoringRows->pluck('scoringID'))
            ->where('actionGoal', 1)
            ->where('actionActive', 1)
            ->get();

        // Goals per "scoringID:teamID" for O(1) per-mini-game lookups.
        $goalsByScoringTeam = [];
        foreach ($goalActions as $a) {
            $key = $a->scoringID . ':' . $a->teamID;
            $goalsByScoringTeam[$key] = ($goalsByScoringTeam[$key] ?? 0) + 1;
        }

        // Per night (gameID): per-team tally [teamID => points] — win = +2 to the
        // winner, draw = +1 each; a mini-game is skipped only if it has no goals
        // and no scoringEnded (not yet played).
        $nightTeamPoints = [];
        foreach ($scoringRows->groupBy('gameID') as $gid => $rows) {
            $tally = [];
            foreach ($rows as $row) {
                $homeGoals = $goalsByScoringTeam[$row->scoringID . ':' . $row->scoringTeamHome] ?? 0;
                $awayGoals = $goalsByScoringTeam[$row->scoringID . ':' . $row->scoringTeamAway] ?? 0;
                if (!$row->scoringEnded && $homeGoals === 0 && $awayGoals === 0) continue;
                if ($homeGoals > $awayGoals) {
                    $tally[$row->scoringTeamHome] = ($tally[$row->scoringTeamHome] ?? 0) + 2;
                } elseif ($awayGoals > $homeGoals) {
                    $tally[$row->scoringTeamAway] = ($tally[$row->scoringTeamAway] ?? 0) + 2;
                } else {
                    $tally[$row->scoringTeamHome] = ($tally[$row->scoringTeamHome] ?? 0) + 1;
                    $tally[$row->scoringTeamAway] = ($tally[$row->scoringTeamAway] ?? 0) + 1;
                }
            }
            $nightTeamPoints[(int) $gid] = $tally;
        }

        // Per player: add their team's night tally for each night they played,
        // looked up via results.resultTeamID for that night.
        $teamPointsByMember = [];
        foreach ($results as $row) {
            $tally    = $nightTeamPoints[(int) $row->resultGameID] ?? [];
            $memberID = (int) $row->resultMemberID;
            $teamPointsByMember[$memberID] = ($teamPointsByMember[$memberID] ?? 0)
                + ($tally[(int) $row->resultTeamID] ?? 0);
        }

        return $teamPointsByMember;
    }
}
