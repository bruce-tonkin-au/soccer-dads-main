<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MemberNightResolver;
use App\Support\MessageMerge;

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

        // Per-night game blocks — one per night this member can access, via the
        // shared resolver (same as /reg). A member who has not been granted a
        // night (e.g. Tuesday) never sees it here.
        $gameBlocks = app(MemberNightResolver::class)
            ->nightsWithNextGame($member)
            ->map(function ($pair) use ($member): array {
                $game = $pair->game;

                $registration = DB::table('game-registrations')
                    ->where('gameID', $game->gameID)
                    ->where('memberID', $member->memberID)
                    ->first();

                $activeCount = DB::table('game-registrations')
                    ->where('gameID', $game->gameID)
                    ->where('registrationStatus', 1)
                    ->where('registrationBench', 0)
                    ->count();

                $onBench = $registration
                    && $registration->registrationStatus == 1
                    && $registration->registrationBench == 1;

                $benchPosition = null;
                if ($onBench) {
                    $benchIds = DB::table('game-registrations')
                        ->where('gameID', $game->gameID)
                        ->where('registrationBench', 1)
                        ->where('registrationStatus', 1)
                        ->orderBy('registrationCreated')
                        ->orderBy('registrationID')
                        ->pluck('registrationID');
                    $idx = $benchIds->search($registration->registrationID);
                    $benchPosition = $idx !== false ? $idx + 1 : 1;
                }

                return [
                    'night'         => $pair->night,
                    'game'          => $game,
                    'registration'  => $registration,
                    'isActive'      => $registration
                        && $registration->registrationStatus == 1
                        && $registration->registrationBench == 0,
                    'onBench'       => $onBench,
                    'atCapacity'    => $activeCount >= 18,
                    'benchPosition' => $benchPosition,
                ];
            });

        $lastRating = DB::table('player-ratings')
            ->where('raterMemberID', $member->memberID)
            ->orderBy('created_at', 'desc')
            ->first();

        $needsPeerReview = !$lastRating ||
            \Carbon\Carbon::parse($lastRating->created_at)->diffInDays(now()) > 14;

        $message->messageBody    = MessageMerge::render($message->messageBody, $member);
        $message->messageSubject = MessageMerge::render($message->messageSubject, $member);

        return view('message', compact(
            'message', 'member', 'gameBlocks', 'needsPeerReview'
        ));
    }

    public function newsletter($messageCode)
    {
        $message = DB::table('messages')
            ->where('messageCode', $messageCode)
            ->where('messageActive', 1)
            ->firstOrFail();

        return view('message-newsletter', [
            'subject' => $message->messageSubject,
            'body'    => $message->messageBody,
        ]);
    }
}
