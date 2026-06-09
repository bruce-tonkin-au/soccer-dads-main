<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Lightweight lookups against the legacy `members` table (no Member model).
 * Members are displayed as "Last, First".
 */
class MemberDirectory
{
    /**
     * Search members by first or last name. Returns [memberID => "Last, First"].
     */
    public static function search(?string $search, int $limit = 50): array
    {
        $query = DB::table('members')
            ->orderBy('memberNameLast')
            ->orderBy('memberNameFirst')
            ->limit($limit);

        if (filled($search)) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('memberNameLast', 'ilike', $term)
                    ->orWhere('memberNameFirst', 'ilike', $term);
            });
        }

        return $query->get(['memberID', 'memberNameFirst', 'memberNameLast'])
            ->mapWithKeys(fn ($m) => [$m->memberID => self::format($m)])
            ->all();
    }

    /**
     * Display label for a single member id, or null if not found.
     */
    public static function label(?int $memberID): ?string
    {
        if (! $memberID) {
            return null;
        }

        $m = DB::table('members')
            ->where('memberID', $memberID)
            ->first(['memberID', 'memberNameFirst', 'memberNameLast']);

        return $m ? self::format($m) : null;
    }

    protected static function format(object $m): string
    {
        $last = trim((string) ($m->memberNameLast ?? ''));
        $first = trim((string) ($m->memberNameFirst ?? ''));

        if ($last !== '' && $first !== '') {
            return "{$last}, {$first}";
        }

        return $last ?: $first ?: "Member #{$m->memberID}";
    }
}
