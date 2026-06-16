<?php

namespace App\Console\Concerns;

use App\Models\WcPlayer;
use Illuminate\Support\Str;

/**
 * Fuzzy resolution of an API-Football player name to a wc_players row, shared by
 * every command that ingests match events (goals and cards). Kept in one place
 * so all ingestion paths match names identically.
 */
trait MatchesWcPlayers
{
    /**
     * Resolve an API player name to one of the fixture's players: exact match,
     * then case-insensitive contains (ILIKE-style), an order-independent
     * token-set match, then a last-name / similarity fallback for abbreviated
     * names like "M. Rashford".
     *
     * @param  \Illuminate\Support\Collection<int, WcPlayer>  $candidates
     */
    protected function matchPlayer(string $name, $candidates): ?WcPlayer
    {
        $target = $this->normalizeName($name);

        // 1. Exact (normalised).
        $exact = $candidates->first(fn (WcPlayer $p) => $this->normalizeName($p->name) === $target);
        if ($exact) {
            return $exact;
        }

        // 2. Contains either way (handles "Marcus Rashford" vs "Rashford").
        $contains = $candidates->first(function (WcPlayer $p) use ($target) {
            $cand = $this->normalizeName($p->name);

            return $cand !== '' && (Str::contains($cand, $target) || Str::contains($target, $cand));
        });
        if ($contains) {
            return $contains;
        }

        // 2.5 Token-set match: same name words in any order. Handles surname-first
        // vs given-name-first ordering, e.g. API "Gi-Hyuk Lee" vs stored
        // "Lee Gi-hyuk". Hyphens are treated as separators here so "Gi-hyuk"
        // and "Gi Hyuk" compare equal. Only accepted when it resolves uniquely.
        $tokenise = fn (string $n) => collect(explode(' ', str_replace('-', ' ', $n)))
            ->filter()
            ->sort()
            ->values()
            ->all();
        $targetTokens = $tokenise($target);
        if (count($targetTokens) > 1) {
            $tokenMatches = $candidates->filter(
                fn (WcPlayer $p) => $tokenise($this->normalizeName($p->name)) === $targetTokens,
            );
            if ($tokenMatches->count() === 1) {
                return $tokenMatches->first();
            }
        }

        // 3. Last-name match when unambiguous (e.g. "M. Rashford" -> "Rashford").
        $targetLast = Str::afterLast($target, ' ');
        if ($targetLast !== '') {
            $lastMatches = $candidates->filter(function (WcPlayer $p) use ($targetLast) {
                return Str::afterLast($this->normalizeName($p->name), ' ') === $targetLast;
            });
            if ($lastMatches->count() === 1) {
                return $lastMatches->first();
            }
        }

        // 4. Best similarity over a high threshold.
        $best = null;
        $bestScore = 0.0;
        foreach ($candidates as $p) {
            similar_text($target, $this->normalizeName($p->name), $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $best = $p;
            }
        }

        return $bestScore >= 85.0 ? $best : null;
    }

    protected function normalizeName(string $name): string
    {
        return Str::of($name)->ascii()->lower()->replace('.', ' ')->squish()->value();
    }
}
