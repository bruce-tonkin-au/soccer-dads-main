<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * The single source of truth for which nights + games a member can see: one
 * entry per accessible night that has an upcoming game. Used by both the /reg
 * registration page and the message page so they can never drift on which
 * nights a member is shown.
 *
 * A night is accessible when member_nights.allowed = 1 AND hidden = 0 and the
 * night is active. Each night's game is the soonest upcoming visible game in a
 * visible season belonging to that night. Nights with no upcoming game are
 * skipped. Callers enrich each pair with their own per-page data.
 */
class MemberNightResolver
{
    /**
     * @return \Illuminate\Support\Collection<int, object{night: object, game: object}>
     */
    public function nightsWithNextGame(object $member)
    {
        $nights = DB::table('member_nights as mn')
            ->join('nights as n', 'mn.nightID', '=', 'n.nightID')
            ->where('mn.memberID', $member->memberID)
            ->where('mn.allowed', 1)
            ->where('mn.hidden', 0)
            ->where('n.nightActive', 1)
            ->orderBy('n.nightSort')
            ->select('n.*')
            ->get();

        return $nights
            ->map(function ($night) {
                $game = $this->nextGameForNight($night->nightID);

                return $game ? (object) ['night' => $night, 'game' => $game] : null;
            })
            ->filter()
            ->values();
    }

    /**
     * The soonest upcoming visible game for a night: gameVisible = 1, in a
     * visible season belonging to that night (seasons.nightID = $nightID),
     * dated today or later (Adelaide).
     */
    public function nextGameForNight(int $nightID): ?object
    {
        return DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameVisible', 1)
            ->where('s.seasonVisible', 1)
            ->where('s.nightID', $nightID)
            ->whereRaw('g."gameDate" >= (NOW() AT TIME ZONE \'Australia/Adelaide\')::date')
            ->orderByRaw('g."gameDate" ASC')
            ->select('g.*', 's.seasonName')
            ->first();
    }
}
