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
            ->whereRaw('LOWER("memberCode") = LOWER(?)', [$memberCode])
            ->where('memberActive', 1)
            ->firstOrFail();

        $currentSeason = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->first();

        $nextGame = null;
        if ($currentSeason) {
            $nextGame = DB::table('games as g')
                ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
                ->where('g.gameVisible', 1)
                ->where('g.gameSeasonID', $currentSeason->seasonID)
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
        $atCapacity   = false;
        $onBench      = false;
        $benchPosition = null;
        if ($nextGame) {
            $registration = DB::table('game-registrations')
                ->where('gameID', $nextGame->gameID)
                ->where('memberID', $member->memberID)
                ->first();

            $activeCount = DB::table('game-registrations')
                ->where('gameID', $nextGame->gameID)
                ->where('registrationStatus', 1)
                ->where('registrationBench', 0)
                ->count();

            $atCapacity = $activeCount >= 18;

            $onBench = $registration
                && $registration->registrationStatus == 1
                && $registration->registrationBench == 1;

            if ($onBench) {
                $benchIds = DB::table('game-registrations')
                    ->where('gameID', $nextGame->gameID)
                    ->where('registrationBench', 1)
                    ->where('registrationStatus', 1)
                    ->orderBy('registrationCreated')
                    ->orderBy('registrationID')
                    ->pluck('registrationID');
                $idx = $benchIds->search($registration->registrationID);
                $benchPosition = $idx !== false ? $idx + 1 : 1;
            }
        }

        $lastRating = DB::table('player-ratings')
            ->where('raterMemberID', $member->memberID)
            ->orderBy('created_at', 'desc')
            ->first();

        $needsPeerReview = !$lastRating ||
            \Carbon\Carbon::parse($lastRating->created_at)->diffInDays(now()) > 14;

        return view('message', compact(
            'message', 'member', 'nextGame', 'registration',
            'needsPeerReview', 'atCapacity', 'onBench', 'benchPosition'
        ));
    }
}
