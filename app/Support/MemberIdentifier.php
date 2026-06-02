<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Generates the unique identifiers every member record needs: a short
 * memberCode used in claim/rate URLs and a memberSlug used in public
 * profile URLs. Centralised here so player creation, editing and any
 * backfill all produce identifiers in the same, collision-free way.
 */
class MemberIdentifier
{
    /**
     * Generate a unique 3-character uppercase member code.
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(3));
        } while (DB::table('members')->where('memberCode', $code)->exists());

        return $code;
    }

    /**
     * Generate a unique slug from a member's name.
     *
     * Pass $excludeMemberID when regenerating for an existing row so the
     * row's own (about-to-be-replaced) slug doesn't count as a collision.
     */
    public static function generateSlug(?string $firstName, ?string $lastName, ?int $excludeMemberID = null): string
    {
        $base = Str::slug(trim(($firstName ?? '') . ' ' . ($lastName ?? '')));

        if ($base === '') {
            $base = $excludeMemberID ? 'player-' . $excludeMemberID : 'player';
        }

        $slug = $base;
        $counter = 2;
        while (
            DB::table('members')
                ->where('memberSlug', $slug)
                ->when($excludeMemberID, fn ($q) => $q->where('memberID', '!=', $excludeMemberID))
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
