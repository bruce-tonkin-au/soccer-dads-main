<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function show($messageCode, $memberCode)
    {
        $message = DB::table('messages')
            ->where('messageCode', $messageCode)
            ->where('messageActive', 1)
            ->firstOrFail();

        $member = DB::table('members')
            ->where('memberCode', strtoupper($memberCode))
            ->where('memberActive', 1)
            ->firstOrFail();

        $currentSeason = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->first();

        $nextGame = null;
        if ($currentSeason) {
            $nextGame = DB::table('games as g')
                ->join('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
                ->where('g.gameVisible', 1)
                ->where('g.gameSeason', $currentSeason->seasonKey)
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))->from('scoring')
                      ->whereColumn('scoring.gameID', 'g.gameID')
                      ->whereNotNull('scoring.scoringEnded');
                })
                ->orderBy('g.gameID', 'asc')
                ->select('g.*', 's.seasonName')
                ->first();
        }

        $registration = null;
        $atCapacity = false;
        if ($nextGame) {
            $registration = DB::table('game-registrations')
                ->where('gameID', $nextGame->gameID)
                ->where('memberID', $member->memberID)
                ->first();

            $confirmedCount = DB::table('game-registrations')
                ->where('gameID', $nextGame->gameID)
                ->where('registrationStatus', 1)
                ->count();

            $atCapacity = $confirmedCount >= 18;
        }

        $lastRating = DB::table('player-ratings')
            ->where('raterMemberID', $member->memberID)
            ->orderBy('created_at', 'desc')
            ->first();

        $needsPeerReview = !$lastRating ||
            \Carbon\Carbon::parse($lastRating->created_at)->diffInDays(now()) > 14;

        return view('message', compact(
            'message', 'member', 'nextGame', 'registration', 'needsPeerReview', 'atCapacity'
        ));
    }
}
