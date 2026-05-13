<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    private function getNextGame()
    {
        $currentSeason = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->first();

        return DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameVisible', 1)
            ->where('g.gameSeasonID', $currentSeason->seasonID)
            ->whereRaw('g."gameDate" >= (NOW() AT TIME ZONE \'Australia/Adelaide\')::date')
            ->orderByRaw('g."gameDate" ASC')
            ->select('g.*', 's.seasonName')
            ->first();
    }

    private function logEvent(int $gameID, int $memberID, string $eventType, ?int $sequence = null): void
    {
        DB::table('game-registration-events')->insert([
            'gameID'               => $gameID,
            'memberID'             => $memberID,
            'eventType'            => $eventType,
            'registrationSequence' => $sequence,
            'created_at'           => now(),
        ]);
    }

    private function promoteFromBench($gameID)
    {
        $firstBench = DB::table('game-registrations as r')
            ->join('members as m', 'r.memberID', '=', 'm.memberID')
            ->where('r.gameID', $gameID)
            ->where('r.registrationBench', 1)
            ->where('r.registrationStatus', 1)
            ->orderBy('r.registrationCreated')
            ->orderBy('r.registrationID')
            ->select('r.*', 'm.memberEmail', 'm.memberNameFirst', 'm.memberCode')
            ->first();

        if (!$firstBench) return;

        DB::table('game-registrations')
            ->where('registrationID', $firstBench->registrationID)
            ->update([
                'registrationBench' => 0,
                'registrationEdited' => now(),
            ]);

        $seq = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('registrationStatus', 1)
            ->where('registrationBench', 0)
            ->count();

        $this->logEvent($gameID, $firstBench->memberID, 'bench_promoted', $seq);

        if ($firstBench->memberEmail) {
            try {
                Mail::send('emails.bench-promotion', [
                    'name' => $firstBench->memberNameFirst,
                    'link' => url('/reg/' . $firstBench->memberCode),
                ], function ($message) use ($firstBench) {
                    $message->to($firstBench->memberEmail)
                            ->subject("You're in! A spot has opened up for Soccer Dads");
                });
            } catch (\Throwable $e) {
                \Log::warning('Bench promotion email failed', ['error' => $e->getMessage()]);
            }
        }
    }

    public function show($memberCode)
    {
        $member = DB::table('members')
            ->whereRaw('LOWER("memberCode") = LOWER(?)', [$memberCode])
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

        $activePlayers = DB::table('game-registrations')
            ->where('gameID', $nextGame->gameID)
            ->where('registrationStatus', 1)
            ->where('registrationBench', 0)
            ->count();

        $benchPosition = null;
        if ($registration && $registration->registrationBench == 1 && $registration->registrationStatus == 1) {
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

        return view('registration', compact(
            'member', 'nextGame', 'registration', 'child',
            'childRegistration', 'balance', 'activePlayers', 'benchPosition'
        ));
    }

    public function update(Request $request, $memberCode)
    {
        $member = DB::table('members')
            ->whereRaw('LOWER("memberCode") = LOWER(?)', [$memberCode])
            ->first();

        if (!$member) abort(404);

        $nextGame = $this->getNextGame();
        if (!$nextGame) abort(404);

        $status      = $request->input('status');
        $childID     = $request->input('childID');
        $childStatus = $request->input('childStatus');

        $benchMessage = null;

        DB::transaction(function () use ($nextGame, $member, $status, $childID, $childStatus, &$benchMessage) {
            // Lock the game row to serialize concurrent registrations for this game.
            // Any other transaction attempting to register for the same game will block
            // here until this transaction commits, preventing the race condition where
            // two requests both read count=17 and both write as the 18th player.
            DB::table('games')->where('gameID', $nextGame->gameID)->lockForUpdate()->first();

            // ── Player registration ───────────────────────────────────────────────
            if ($status !== null) {
                $existing = DB::table('game-registrations')
                    ->where('gameID', $nextGame->gameID)
                    ->where('memberID', $member->memberID)
                    ->first();

                $wasActive = $existing && $existing->registrationStatus == 1 && $existing->registrationBench == 0;

                if ($status == 1) {
                    $alreadyActive = $existing && $existing->registrationStatus == 1 && $existing->registrationBench == 0;

                    if (!$alreadyActive) {
                        $activeCount = DB::table('game-registrations')
                            ->where('gameID', $nextGame->gameID)
                            ->where('registrationStatus', 1)
                            ->where('registrationBench', 0)
                            ->count();

                        if ($activeCount >= 18) {
                            if ($existing) {
                                DB::table('game-registrations')
                                    ->where('registrationID', $existing->registrationID)
                                    ->update([
                                        'registrationStatus' => 1,
                                        'registrationBench'  => 1,
                                        'registrationEdited' => now(),
                                    ]);
                            } else {
                                DB::table('game-registrations')->insert([
                                    'gameID'              => $nextGame->gameID,
                                    'memberID'            => $member->memberID,
                                    'registrationStatus'  => 1,
                                    'registrationBench'   => 1,
                                    'registrationCreated' => now(),
                                    'registrationEdited'  => now(),
                                ]);
                            }
                            $this->logEvent($nextGame->gameID, $member->memberID, 'bench_added');
                            $benchMessage = "You've been added to the reserves bench. You'll be automatically added to the game if a spot opens up.";
                            return;
                        }
                    }

                    if ($existing) {
                        DB::table('game-registrations')
                            ->where('registrationID', $existing->registrationID)
                            ->update([
                                'registrationStatus' => 1,
                                'registrationBench'  => 0,
                                'registrationEdited' => now(),
                            ]);
                    } else {
                        DB::table('game-registrations')->insert([
                            'gameID'              => $nextGame->gameID,
                            'memberID'            => $member->memberID,
                            'registrationStatus'  => 1,
                            'registrationBench'   => 0,
                            'registrationCreated' => now(),
                            'registrationEdited'  => now(),
                        ]);
                    }

                    $seq = DB::table('game-registrations')
                        ->where('gameID', $nextGame->gameID)
                        ->where('registrationStatus', 1)
                        ->where('registrationBench', 0)
                        ->count();
                    $this->logEvent($nextGame->gameID, $member->memberID, 'registered', $seq);
                } else {
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

                    if ($status == 2 && $wasActive) {
                        $this->logEvent($nextGame->gameID, $member->memberID, 'deregistered');
                        $this->promoteFromBench($nextGame->gameID);
                    }
                }
            }

            if ($benchMessage) return; // player was benched — skip child processing

            // ── Child registration ────────────────────────────────────────────────
            if ($childID && $childStatus !== null) {
                $childExisting = DB::table('game-registrations')
                    ->where('gameID', $nextGame->gameID)
                    ->where('memberID', $childID)
                    ->first();

                $childWasActive = $childExisting && $childExisting->registrationStatus == 1 && $childExisting->registrationBench == 0;

                if ($childStatus == 1) {
                    $childAlreadyActive = $childExisting && $childExisting->registrationStatus == 1 && $childExisting->registrationBench == 0;

                    if (!$childAlreadyActive) {
                        $activeCount = DB::table('game-registrations')
                            ->where('gameID', $nextGame->gameID)
                            ->where('registrationStatus', 1)
                            ->where('registrationBench', 0)
                            ->count();

                        if ($activeCount >= 18) {
                            if ($childExisting) {
                                DB::table('game-registrations')
                                    ->where('registrationID', $childExisting->registrationID)
                                    ->update([
                                        'registrationStatus' => 1,
                                        'registrationBench'  => 1,
                                        'registrationEdited' => now(),
                                    ]);
                            } else {
                                DB::table('game-registrations')->insert([
                                    'gameID'              => $nextGame->gameID,
                                    'memberID'            => $childID,
                                    'registrationStatus'  => 1,
                                    'registrationBench'   => 1,
                                    'registrationCreated' => now(),
                                    'registrationEdited'  => now(),
                                ]);
                            }
                            $this->logEvent($nextGame->gameID, $childID, 'bench_added');
                            $benchMessage = "Your child has been added to the reserves bench.";
                            return;
                        }
                    }

                    if ($childExisting) {
                        DB::table('game-registrations')
                            ->where('registrationID', $childExisting->registrationID)
                            ->update([
                                'registrationStatus' => 1,
                                'registrationBench'  => 0,
                                'registrationEdited' => now(),
                            ]);
                    } else {
                        DB::table('game-registrations')->insert([
                            'gameID'              => $nextGame->gameID,
                            'memberID'            => $childID,
                            'registrationStatus'  => 1,
                            'registrationBench'   => 0,
                            'registrationCreated' => now(),
                            'registrationEdited'  => now(),
                        ]);
                    }

                    $seq = DB::table('game-registrations')
                        ->where('gameID', $nextGame->gameID)
                        ->where('registrationStatus', 1)
                        ->where('registrationBench', 0)
                        ->count();
                    $this->logEvent($nextGame->gameID, $childID, 'registered', $seq);
                } else {
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

                    if ($childStatus == 2 && $childWasActive) {
                        $this->logEvent($nextGame->gameID, $childID, 'deregistered');
                        $this->promoteFromBench($nextGame->gameID);
                    }
                }
            }
        });

        if ($benchMessage) {
            return redirect("/reg/{$memberCode}")->with('bench', $benchMessage);
        }
        return redirect("/reg/{$memberCode}")->with('success', 'Registration updated!');
    }
}
