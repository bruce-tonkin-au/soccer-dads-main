<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for the game-registration mutations. The legacy
 * AdminController methods (registerPlayer/demotePlayer/promotePlayer/moveBench/
 * toggleBench), the dashboard quick-panel and the Filament Registrations page
 * all call these methods so the status/bench writes, the registrationBenchOrder
 * normalisation and the game-registration-events logging never drift.
 *
 * Each method returns a structured array (no HTTP responses) — callers map the
 * result to their own JSON / redirect / notification.
 */
class RegistrationActions
{
    /**
     * Register (or re-activate) a member for a game: upsert to status=1/bench=0
     * and log an admin_registered event with the live active-player sequence.
     *
     * @return array{success: bool, sequence: int}
     */
    public function register($gameID, $memberID): array
    {
        $existing = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->first();

        if ($existing) {
            DB::table('game-registrations')
                ->where('gameID', $gameID)
                ->where('memberID', $memberID)
                ->update([
                    'registrationStatus' => 1,
                    'registrationBench'  => 0,
                    'registrationEdited' => now(),
                ]);
        } else {
            DB::table('game-registrations')->insert([
                'gameID'              => $gameID,
                'memberID'            => $memberID,
                'registrationStatus'  => 1,
                'registrationBench'   => 0,
                'registrationCreated' => now(),
                'registrationEdited'  => now(),
            ]);
        }

        $seq = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('registrationStatus', 1)
            ->where('registrationBench', 0)
            ->count();

        DB::table('game-registration-events')->insert([
            'gameID'               => $gameID,
            'memberID'             => $memberID,
            'eventType'            => 'admin_registered',
            'registrationSequence' => $seq,
            'created_at'           => now(),
        ]);

        return ['success' => true, 'sequence' => $seq];
    }

    /**
     * Mark a member as not going (status=2, bench=0). Logs an
     * admin_deregistered event only when they were previously active.
     *
     * @return array{success: bool, wasActive: bool}
     */
    public function deregister($gameID, $memberID): array
    {
        $existing = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->first();

        $wasActive = $existing && $existing->registrationStatus == 1 && $existing->registrationBench == 0;

        DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->update([
                'registrationStatus' => 2,
                'registrationBench'  => 0,
                'registrationEdited' => now(),
            ]);

        if ($wasActive) {
            DB::table('game-registration-events')->insert([
                'gameID'               => $gameID,
                'memberID'             => $memberID,
                'eventType'            => 'admin_deregistered',
                'registrationSequence' => null,
                'created_at'           => now(),
            ]);
        }

        return ['success' => true, 'wasActive' => $wasActive];
    }

    /**
     * Promote a bench player to active (bench=0). Logs a bench_promoted event
     * with the live active-player sequence.
     *
     * @return array{success: bool, sequence: int}
     */
    public function promote($gameID, $memberID): array
    {
        DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->where('registrationStatus', 1)
            ->update([
                'registrationBench'  => 0,
                'registrationEdited' => now(),
            ]);

        $seq = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('registrationStatus', 1)
            ->where('registrationBench', 0)
            ->count();

        DB::table('game-registration-events')->insert([
            'gameID'               => $gameID,
            'memberID'             => $memberID,
            'eventType'            => 'bench_promoted',
            'registrationSequence' => $seq,
            'created_at'           => now(),
        ]);

        return ['success' => true, 'sequence' => $seq];
    }

    /**
     * Move a bench player up/down the queue by swapping with the adjacent bench
     * player, then normalise registrationBenchOrder to 1..N (in a transaction).
     * No-op (changed=false) when the player isn't on the bench or is already at
     * the end being moved toward.
     *
     * @return array{success: bool, changed: bool}
     */
    public function moveBench($gameID, $memberID, $direction): array
    {
        $bench = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('registrationStatus', 1)
            ->where('registrationBench', 1)
            ->orderByRaw('("registrationBenchOrder" = 0)')
            ->orderBy('registrationBenchOrder')
            ->orderBy('registrationCreated')
            ->orderBy('registrationID')
            ->get(['registrationID', 'memberID']);

        $ids = $bench->pluck('registrationID')->values()->all();
        $pos = $bench->search(fn ($r) => $r->memberID == $memberID);

        if ($pos === false) {
            return ['success' => true, 'changed' => false];
        }

        $swap = $direction === 'up' ? $pos - 1 : $pos + 1;
        if ($swap < 0 || $swap >= count($ids)) {
            return ['success' => true, 'changed' => false];
        }

        [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];

        DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $registrationID) {
                DB::table('game-registrations')
                    ->where('registrationID', $registrationID)
                    ->update(['registrationBenchOrder' => $i + 1]);
            }
        });

        return ['success' => true, 'changed' => true];
    }

    /**
     * Toggle a member's bench flag (0<->1). found=false when there is no
     * registration row for the member/game.
     *
     * @return array{success: bool, found: bool, bench?: int}
     */
    public function toggleBench($gameID, $memberID): array
    {
        $reg = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->first();

        if (! $reg) {
            return ['success' => false, 'found' => false];
        }

        $newValue = $reg->registrationBench ? 0 : 1;

        DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->update(['registrationBench' => $newValue]);

        return ['success' => true, 'found' => true, 'bench' => $newValue];
    }
}
