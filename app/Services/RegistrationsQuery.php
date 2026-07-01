<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Builds everything the registrations screen needs for a given game. Mirrors
 * AdminController::registrations() exactly so the legacy Blade screen and the
 * Filament Registrations page share one data path.
 *
 * Returns an array with the same keys the legacy view compacts:
 * game, allGames, registrations, events, isNextGame, nextGame, unregistered.
 * game is null when no game can be resolved (caller decides 404 vs empty state).
 */
class RegistrationsQuery
{
    /** @return array<string, mixed> */
    public function data(?int $gameID = null): array
    {
        $currentSeason = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->first();

        $nextGame = null;
        if ($currentSeason) {
            $nextGame = DB::table('games')
                ->where('gameVisible', 1)
                ->where('gameSeasonID', $currentSeason->seasonID)
                ->whereRaw('"gameDate" >= (NOW() AT TIME ZONE \'Australia/Adelaide\')::date')
                ->orderBy('gameDate', 'asc')
                ->orderBy('gameID', 'asc')
                ->first();
        }

        $allGames = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameVisible', 1)
            ->orderBy('g.gameDate', 'desc')
            ->orderBy('g.gameID', 'desc')
            ->select('g.gameID', 'g.gameRound', 'g.gameDate', 's.seasonName')
            ->get();

        if (! $gameID) {
            $gameID = $nextGame ? $nextGame->gameID : ($allGames->first() ? $allGames->first()->gameID : null);
        }

        // No game to show — caller decides how to handle (legacy aborts 404).
        if (! $gameID) {
            return [
                'game'          => null,
                'allGames'      => $allGames,
                'registrations' => collect(),
                'events'        => collect(),
                'isNextGame'    => false,
                'nextGame'      => $nextGame,
                'unregistered'  => collect(),
            ];
        }

        $game = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameID', $gameID)
            ->select('g.*', 's.seasonName')
            ->first();

        if (! $game) {
            return [
                'game'          => null,
                'allGames'      => $allGames,
                'registrations' => collect(),
                'events'        => collect(),
                'isNextGame'    => false,
                'nextGame'      => $nextGame,
                'unregistered'  => collect(),
            ];
        }

        // All registrations ordered by when they first registered
        $registrations = DB::table('game-registrations as r')
            ->join('members as m', 'r.memberID', '=', 'm.memberID')
            ->where('r.gameID', $gameID)
            ->orderBy('r.registrationCreated', 'asc')
            ->orderBy('r.registrationID', 'asc')
            ->select('r.*', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug')
            ->get();

        // Bench queue order (B1, B2, …): explicit registrationBenchOrder when set,
        // otherwise registrationCreated — matches the promotion order. Maps
        // registrationID => 1-based bench position.
        $benchRank = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('registrationStatus', 1)
            ->where('registrationBench', 1)
            ->orderByRaw('("registrationBenchOrder" = 0)')
            ->orderBy('registrationBenchOrder')
            ->orderBy('registrationCreated')
            ->orderBy('registrationID')
            ->pluck('registrationID')
            ->flip()
            ->map(fn ($i) => $i + 1);

        // Assign registration sequence: active players ranked by registrationCreated,
        // bench players by the bench queue order computed above.
        $activeSeq = 0;
        $registrations = $registrations->map(function ($r) use (&$activeSeq, $benchRank) {
            $r->activeSequence = null;
            $r->benchSequence  = null;
            if ($r->registrationStatus == 1 && $r->registrationBench == 0) {
                $r->activeSequence = ++$activeSeq;
            } elseif ($r->registrationStatus == 1 && $r->registrationBench == 1) {
                $r->benchSequence = $benchRank[$r->registrationID] ?? null;
            }
            return $r;
        });

        // Event log for this game (from the events table)
        $events = DB::table('game-registration-events as e')
            ->join('members as m', 'e.memberID', '=', 'm.memberID')
            ->where('e.gameID', $gameID)
            ->orderBy('e.created_at', 'asc')
            ->orderBy('e.eventID', 'asc')
            ->select('e.*', 'm.memberNameFirst', 'm.memberNameLast')
            ->get();

        $isNextGame = $nextGame && $nextGame->gameID == $gameID;

        // Games played THIS SEASON per member — priority for the final round.
        $seasonGamesPlayed = DB::table('results')
            ->where('resultSeasonID', $game->gameSeasonID)
            ->where('resultActive', 1)
            ->groupBy('resultMemberID')
            ->select('resultMemberID', DB::raw('COUNT(DISTINCT "resultGameID") as "gamesPlayed"'))
            ->pluck('gamesPlayed', 'resultMemberID');

        // Members already in this game's registration list (any status) — excluded from the pool.
        $registeredMemberIDs = DB::table('game-registrations')
            ->where('gameID', $game->gameID)
            ->pluck('memberID');

        // Active members who played this season but aren't registered for this game,
        // ordered by most games played first (priority), then surname.
        $unregistered = collect();
        if ($seasonGamesPlayed->isNotEmpty()) {
            $unregistered = DB::table('members')
                ->where('memberActive', 1)
                ->whereIn('memberID', $seasonGamesPlayed->keys())
                ->when($registeredMemberIDs->isNotEmpty(),
                    fn ($q) => $q->whereNotIn('memberID', $registeredMemberIDs))
                ->select('memberID', 'memberNameFirst', 'memberNameLast', 'memberSlug')
                ->get()
                ->map(function ($m) use ($seasonGamesPlayed) {
                    $m->gamesPlayed = (int) ($seasonGamesPlayed[$m->memberID] ?? 0);
                    return $m;
                })
                ->sortBy([
                    ['gamesPlayed', 'desc'],
                    ['memberNameLast', 'asc'],
                    ['memberNameFirst', 'asc'],
                ])
                ->values();
        }

        return compact(
            'game', 'allGames', 'registrations', 'events', 'isNextGame', 'nextGame', 'unregistered'
        );
    }
}
