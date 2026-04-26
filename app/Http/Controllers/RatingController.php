<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RatingController extends Controller
{
    public function show($memberCode)
    {
        $rater = DB::table('members')
            ->where('memberCode', $memberCode)
            ->where('memberActive', 1)
            ->firstOrFail();

        $threeMonthsAgo = now()->subMonths(3);

        $myRecentGames = DB::table('results as r')
            ->join('games as g', 'r.resultGame', '=', 'g.gameKey')
            ->where('r.resultMember', $rater->memberKey)
            ->where('r.resultActive', 1)
            ->where(function ($q) use ($threeMonthsAgo) {
                $q->whereRaw("STR_TO_DATE(g.gameDate, '%e/%c/%Y') >= ?", [$threeMonthsAgo->format('Y-m-d')])
                  ->orWhere('g.gameDate', '>=', $threeMonthsAgo->format('Y-m-d'));
            })
            ->pluck('r.resultGame');

        $recentTeammates = DB::table('results as r')
            ->join('members as m', 'r.resultMember', '=', 'm.memberKey')
            ->whereIn('r.resultGame', $myRecentGames)
            ->where('r.resultMember', '!=', $rater->memberKey)
            ->where('m.memberActive', 1)
            ->whereNotNull('m.memberKey')
            ->select('m.memberID', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug', 'm.memberPhoto')
            ->distinct()
            ->get();

        $currentSeason = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->first();

        $nextGame = DB::table('games')
            ->where('gameVisible', 1)
            ->where('gameSeason', $currentSeason->seasonKey)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('scoring')
                  ->whereColumn('scoring.gameID', 'games.gameID')
                  ->whereNotNull('scoring.scoringEnded');
            })
            ->orderBy('gameID', 'asc')
            ->first();

        $nextGameRegistered = collect();
        if ($nextGame) {
            $nextGameRegistered = DB::table('game-registrations as r')
                ->join('members as m', 'r.memberID', '=', 'm.memberID')
                ->where('r.gameID', $nextGame->gameID)
                ->where('r.registrationStatus', 1)
                ->where('m.memberID', '!=', $rater->memberID)
                ->where('m.memberActive', 1)
                ->select('m.memberID', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug', 'm.memberPhoto')
                ->get();
        }

        $teammates = $recentTeammates
            ->merge($nextGameRegistered)
            ->unique('memberID')
            ->values();

        $teammateKeys = DB::table('members')
            ->whereIn('memberID', $teammates->pluck('memberID'))
            ->pluck('memberKey', 'memberID');

        $playedMemberKeys = DB::table('results')
            ->whereIn('resultMember', $teammateKeys->values())
            ->where('resultActive', 1)
            ->distinct('resultMember')
            ->pluck('resultMember')
            ->toArray();

        $teammates = $teammates->filter(function ($player) use ($teammateKeys, $playedMemberKeys) {
            $key = $teammateKeys[$player->memberID] ?? null;
            return $key && in_array($key, $playedMemberKeys);
        })->values();

        $alreadyRated = DB::table('player-ratings')
            ->where('raterMemberID', $rater->memberID)
            ->pluck('ratedMemberID')
            ->toArray();

        $skippedIDs = session("rating_skips_{$memberCode}", []);

        $nextPlayer = $teammates->first(
            fn($p) => !in_array($p->memberID, $alreadyRated) && !in_array($p->memberID, $skippedIDs)
        );

        $totalToRate = $teammates->count();
        $ratedCount  = count(array_intersect($teammates->pluck('memberID')->toArray(), $alreadyRated));

        if (!$nextPlayer) {
            return redirect("/rate/{$memberCode}/done");
        }

        return view('rating.show', compact('rater', 'nextPlayer', 'totalToRate', 'ratedCount'));
    }

    public function store(Request $request, $memberCode)
    {
        $rater = DB::table('members')
            ->where('memberCode', $memberCode)
            ->where('memberActive', 1)
            ->firstOrFail();

        $ratedMemberID = $request->input('ratedMemberID');
        $action        = $request->input('action');

        Log::info('Rating store called', [
            'action'        => $action,
            'ratedMemberID' => $ratedMemberID,
            'all'           => $request->all(),
        ]);

        if ($action === 'skip') {
            $sessionKey = "rating_skips_{$memberCode}";
            $skipped = session($sessionKey, []);
            $skipped[] = (int) $ratedMemberID;
            session([$sessionKey => array_unique($skipped)]);

            return redirect("/rate/{$memberCode}");
        }

        $existing = DB::table('player-ratings')
            ->where('raterMemberID', $rater->memberID)
            ->where('ratedMemberID', $ratedMemberID)
            ->first();

        if ($existing) {
            DB::table('player-ratings')
                ->where('ratingID', $existing->ratingID)
                ->update([
                    'ratingGoal'      => $request->input('ratingGoal', 0),
                    'ratingPassing'   => $request->input('ratingPassing', 0),
                    'ratingWork'      => $request->input('ratingWork', 0),
                    'ratingDefending' => $request->input('ratingDefending', 0),
                    'ratingOverall'   => $request->input('ratingOverall', 0),
                    'updated_at'      => now(),
                ]);
        } else {
            DB::table('player-ratings')->insert([
                'raterMemberID'   => $rater->memberID,
                'ratedMemberID'   => $ratedMemberID,
                'ratingGoal'      => $request->input('ratingGoal', 0),
                'ratingPassing'   => $request->input('ratingPassing', 0),
                'ratingWork'      => $request->input('ratingWork', 0),
                'ratingDefending' => $request->input('ratingDefending', 0),
                'ratingOverall'   => $request->input('ratingOverall', 0),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return redirect("/rate/{$memberCode}");
    }

    public function done($memberCode)
    {
        $rater = DB::table('members')
            ->where('memberCode', $memberCode)
            ->where('memberActive', 1)
            ->firstOrFail();

        $totalRated = DB::table('player-ratings')
            ->where('raterMemberID', $rater->memberID)
            ->count();

        return view('rating.done', compact('rater', 'totalRated'));
    }
}
