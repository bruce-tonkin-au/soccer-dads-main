<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    private function getNextGame()
{
    $currentSeason = DB::table('seasons')
        ->where('seasonVisible', 1)
        ->orderBy('seasonID', 'desc')
        ->first();

    return DB::table('games as g')
        ->join('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
        ->where('g.gameVisible', 1)
        ->where('g.gameSeason', $currentSeason->seasonKey)
        ->orderBy('g.gameID', 'asc')
        ->select('g.*', 's.seasonName')
        ->first();
}

    public function show($memberCode)
    {
        $member = DB::table('members')
            ->where('memberCode', $memberCode)
            ->first();

        if (!$member) abort(404);

        $nextGame = $this->getNextGame();

        if (!$nextGame) abort(404);

        $registration = DB::table('game-registrations')
            ->where('gameID', $nextGame->gameID)
            ->where('memberID', $member->memberID)
            ->first();

        $child = DB::table('members')
            ->where('memberParent', $member->memberID)
            ->where('memberActive', 1)
            ->first();

        $childRegistration = null;
        if ($child) {
            $childRegistration = DB::table('game-registrations')
                ->where('gameID', $nextGame->gameID)
                ->where('memberID', $child->memberID)
                ->first();
        }

        $balance = DB::table('account')
            ->where('memberID', $member->memberID)
            ->where('accountVisible', 1)
            ->sum('accountValue');

        $totalPlayers = DB::table('game-registrations')
            ->where('gameID', $nextGame->gameID)
            ->where('registrationStatus', 1)
            ->count();

        return view('registration', compact(
            'member', 'nextGame', 'registration', 'child',
            'childRegistration', 'balance', 'totalPlayers'
        ));
    }

    public function update(Request $request, $memberCode)
{
    $member = DB::table('members')
        ->where('memberCode', $memberCode)
        ->first();

    if (!$member) abort(404);

    $nextGame = $this->getNextGame();
    if (!$nextGame) abort(404);

    $status = $request->input('status');
    $childID = $request->input('childID');
    $childStatus = $request->input('childStatus');

    // Only update parent if status was submitted
    if ($status !== null) {
    $existing = DB::table('game-registrations')
        ->where('gameID', $nextGame->gameID)
        ->where('memberID', $member->memberID)
        ->first();

    // Check cap for new attendees
    if ($status == 1 && $existing?->registrationStatus != 1) {
        $currentCount = DB::table('game-registrations')
            ->where('gameID', $nextGame->gameID)
            ->where('registrationStatus', 1)
            ->count();
        
        if ($currentCount >= 18) {
            return redirect("/reg/{$memberCode}")->with('error', 'Sorry — this game is full!');
        }
    }

    if ($existing) {
            DB::table('game-registrations')
                ->where('registrationID', $existing->registrationID)
                ->update([
                    'registrationStatus' => $status,
                    'registrationEdited' => now(),
                ]);
        } else {
            DB::table('game-registrations')->insert([
                'gameID'              => $nextGame->gameID,
                'memberID'            => $member->memberID,
                'registrationStatus'  => $status,
                'registrationCreated' => now(),
                'registrationEdited'  => now(),
            ]);
        }
    }

    // Only update child if childStatus was submitted
    if ($childID && $childStatus !== null) {
        $childExisting = DB::table('game-registrations')
            ->where('gameID', $nextGame->gameID)
            ->where('memberID', $childID)
            ->first();

        if ($childExisting) {
            DB::table('game-registrations')
                ->where('registrationID', $childExisting->registrationID)
                ->update([
                    'registrationStatus' => $childStatus,
                    'registrationEdited' => now(),
                ]);
        } else {
            DB::table('game-registrations')->insert([
                'gameID'              => $nextGame->gameID,
                'memberID'            => $childID,
                'registrationStatus'  => $childStatus,
                'registrationCreated' => now(),
                'registrationEdited'  => now(),
            ]);
        }
    }

    return redirect("/reg/{$memberCode}")->with('success', 'Registration updated!');
}
}