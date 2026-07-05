<?php

namespace App\Support;

/**
 * Per-night accent colour (site palette), keyed by night name so a night gets
 * the SAME colour everywhere it is shown — the /reg registration page and the
 * message page, whether the member has one night or two.
 *
 * Friday = blue, Tuesday = green; any other/future night falls back to gold.
 */
class NightColour
{
    private const MAP = [
        'Friday'  => '#458bc8', // blue
        'Tuesday' => '#7bba56', // green
    ];

    private const FALLBACK = '#e68a46'; // gold

    public static function accent(?string $nightName): string
    {
        return self::MAP[$nightName ?? ''] ?? self::FALLBACK;
    }
}
