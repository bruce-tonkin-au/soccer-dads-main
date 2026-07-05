<?php

namespace App\Http\Controllers;

use App\Services\MemberNightResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    /**
     * The single source of truth for which nights + games a member may register
     * for. Returns one "block" per accessible night that has an upcoming game.
     * Both show() and update() use this, so the rendered page and the POST
     * handler can never disagree about what a member is allowed to touch.
     *
     * Accessible night: member_nights.allowed = 1 AND hidden = 0 AND the night
     * is active, ordered by nightSort. Each night's next game is resolved by
     * nextGameForNight() and nights with no upcoming game are skipped.
     *
     * @return \Illuminate\Support\Collection<int,array{
     *   night:object, game:object, registration:?object, activePlayers:int,
     *   benchPosition:?int, child:?object, childRegistration:?object
     * }>
     */
    private function resolveNightBlocks(object $member)
    {
        // The member's active child is per-member, not per-night — resolve once.
        $child = DB::table('members')
            ->where('memberParent', $member->memberID)
            ->where('memberActive', 1)
            ->first();

        // Accessible nights + their next game come from the shared resolver so
        // /reg and the message page can never disagree on what a member sees.
        return app(MemberNightResolver::class)
            ->nightsWithNextGame($member)
            ->map(function ($pair) use ($member, $child): array {
                $night = $pair->night;
                $game  = $pair->game;

                $registration = DB::table('game-registrations')
                    ->where('gameID', $game->gameID)
                    ->where('memberID', $member->memberID)
                    ->first();

                $childRegistration = null;
                if ($child) {
                    $childRegistration = DB::table('game-registrations')
                        ->where('gameID', $game->gameID)
                        ->where('memberID', $child->memberID)
                        ->first();
                }

                $activePlayers = DB::table('game-registrations')
                    ->where('gameID', $game->gameID)
                    ->where('registrationStatus', 1)
                    ->where('registrationBench', 0)
                    ->count();

                $benchPosition = null;
                if ($registration && $registration->registrationBench == 1 && $registration->registrationStatus == 1) {
                    $benchIds = DB::table('game-registrations')
                        ->where('gameID', $game->gameID)
                        ->where('registrationBench', 1)
                        ->where('registrationStatus', 1)
                        ->orderByRaw('("registrationBenchOrder" = 0)')
                        ->orderBy('registrationBenchOrder')
                        ->orderBy('registrationCreated')
                        ->orderBy('registrationID')
                        ->pluck('registrationID');
                    $idx = $benchIds->search($registration->registrationID);
                    $benchPosition = $idx !== false ? $idx + 1 : 1;
                }

                return [
                    'night'             => $night,
                    'game'              => $game,
                    'registration'      => $registration,
                    'activePlayers'     => $activePlayers,
                    'benchPosition'     => $benchPosition,
                    'child'             => $child,
                    'childRegistration' => $childRegistration,
                ];
            });
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

    // Returns the promoted member object so the caller can send the email
    // after the enclosing transaction commits. Never throws.
    private function promoteFromBench(int $gameID): ?object
    {
        $firstBench = DB::table('game-registrations as r')
            ->join('members as m', 'r.memberID', '=', 'm.memberID')
            ->where('r.gameID', $gameID)
            ->where('r.registrationBench', 1)
            ->where('r.registrationStatus', 1)
            ->orderByRaw('(r."registrationBenchOrder" = 0)')
            ->orderBy('r.registrationBenchOrder')
            ->orderBy('r.registrationCreated')
            ->orderBy('r.registrationID')
            ->select('r.*', 'm.memberEmail', 'm.memberNameFirst', 'm.memberCode')
            ->first();

        if (!$firstBench) return null;

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

        return $firstBench;
    }

    // Called after the transaction has committed so a mail failure cannot roll
    // back the promotion. Logs but never throws.
    private function sendBenchPromotionEmail(object $member): void
    {
        if (!$member->memberEmail) {
            \Log::info('Skipped bench promotion email: no contact details', [
                'memberID' => $member->memberID,
            ]);
            return;
        }

        try {
            Mail::send('emails.bench-promotion', [
                'name' => $member->memberNameFirst,
                'link' => url('/reg/' . $member->memberCode),
            ], function ($message) use ($member) {
                $message->to($member->memberEmail)
                        ->subject("You're in! A spot has opened up for Soccer Dads");
            });
        } catch (\Throwable $e) {
            \Log::warning('Bench promotion email failed', [
                'memberID' => $member->memberID,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    public function show($memberCode)
    {
        $member = DB::table('members')
            ->whereRaw('LOWER("memberCode") = LOWER(?)', [$memberCode])
            ->first();

        if (!$member) abort(404);

        // One section per accessible night with an upcoming game (single source
        // of truth — the same resolver validates the POST in update()).
        $blocks = $this->resolveNightBlocks($member);

        // Account balance is per member, not per night — a single figure below.
        $balance = DB::table('account')
            ->where('memberID', $member->memberID)
            ->where('accountVisible', 1)
            ->sum('accountValue');

        return view('registration', compact('member', 'blocks', 'balance'));
    }

    public function update(Request $request, $memberCode)
    {
        $member = DB::table('members')
            ->whereRaw('LOWER("memberCode") = LOWER(?)', [$memberCode])
            ->first();

        if (!$member) abort(404);

        // Never trust the posted gameID: it must be the resolved game for one of
        // the member's own accessible night blocks. This is the same resolver
        // show() uses, so a member can only POST to games they can actually see.
        $gameID = (int) $request->input('gameID');
        $block  = $this->resolveNightBlocks($member)
            ->first(fn ($b) => (int) $b['game']->gameID === $gameID);

        if (!$block) abort(403);

        $nextGame = $block['game']; // keep the name so the transaction below is unchanged

        $status      = $request->input('status');
        $childID     = $request->input('childID');
        $childStatus = $request->input('childStatus');

        $benchMessage   = null;
        $promotedMembers = [];

        DB::transaction(function () use ($nextGame, $member, $status, $childID, $childStatus, &$benchMessage, &$promotedMembers) {
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
                        $promoted = $this->promoteFromBench($nextGame->gameID);
                        if ($promoted) $promotedMembers[] = $promoted;
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
                        $promoted = $this->promoteFromBench($nextGame->gameID);
                        if ($promoted) $promotedMembers[] = $promoted;
                    }
                }
            }
        });

        // Send notifications after the transaction has committed so email failures
        // cannot roll back the promotion.
        foreach ($promotedMembers as $promoted) {
            $this->sendBenchPromotionEmail($promoted);
        }

        if ($benchMessage) {
            return redirect("/reg/{$memberCode}")->with('bench', $benchMessage);
        }
        return redirect("/reg/{$memberCode}")->with('success', 'Registration updated!');
    }
}
