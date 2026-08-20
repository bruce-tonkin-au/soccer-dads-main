<?php

namespace App\Support;

class Countries
{
    /**
     * ISO alpha-2 code => ['name' => ..., 'flag' => ...]. Lifted from the portal
     * list — the canonical set every country picker shares. AU first.
     */
    public static function all(): array
    {
        return [
            'AU' => ['name' => 'Australia', 'flag' => '🇦🇺'],
            'AF' => ['name' => 'Afghanistan', 'flag' => '🇦🇫'],
            'AL' => ['name' => 'Albania', 'flag' => '🇦🇱'],
            'DZ' => ['name' => 'Algeria', 'flag' => '🇩🇿'],
            'AR' => ['name' => 'Argentina', 'flag' => '🇦🇷'],
            'AT' => ['name' => 'Austria', 'flag' => '🇦🇹'],
            'BE' => ['name' => 'Belgium', 'flag' => '🇧🇪'],
            'BR' => ['name' => 'Brazil', 'flag' => '🇧🇷'],
            'BG' => ['name' => 'Bulgaria', 'flag' => '🇧🇬'],
            'CA' => ['name' => 'Canada', 'flag' => '🇨🇦'],
            'CL' => ['name' => 'Chile', 'flag' => '🇨🇱'],
            'CN' => ['name' => 'China', 'flag' => '🇨🇳'],
            'CO' => ['name' => 'Colombia', 'flag' => '🇨🇴'],
            'HR' => ['name' => 'Croatia', 'flag' => '🇭🇷'],
            'CZ' => ['name' => 'Czech Republic', 'flag' => '🇨🇿'],
            'DK' => ['name' => 'Denmark', 'flag' => '🇩🇰'],
            'EG' => ['name' => 'Egypt', 'flag' => '🇪🇬'],
            'ET' => ['name' => 'Ethiopia', 'flag' => '🇪🇹'],
            'FI' => ['name' => 'Finland', 'flag' => '🇫🇮'],
            'FR' => ['name' => 'France', 'flag' => '🇫🇷'],
            'DE' => ['name' => 'Germany', 'flag' => '🇩🇪'],
            'GH' => ['name' => 'Ghana', 'flag' => '🇬🇭'],
            'GR' => ['name' => 'Greece', 'flag' => '🇬🇷'],
            'HU' => ['name' => 'Hungary', 'flag' => '🇭🇺'],
            'IN' => ['name' => 'India', 'flag' => '🇮🇳'],
            'ID' => ['name' => 'Indonesia', 'flag' => '🇮🇩'],
            'IR' => ['name' => 'Iran', 'flag' => '🇮🇷'],
            'IQ' => ['name' => 'Iraq', 'flag' => '🇮🇶'],
            'IE' => ['name' => 'Ireland', 'flag' => '🇮🇪'],
            'IL' => ['name' => 'Israel', 'flag' => '🇮🇱'],
            'IT' => ['name' => 'Italy', 'flag' => '🇮🇹'],
            'JP' => ['name' => 'Japan', 'flag' => '🇯🇵'],
            'JO' => ['name' => 'Jordan', 'flag' => '🇯🇴'],
            'KE' => ['name' => 'Kenya', 'flag' => '🇰🇪'],
            'KR' => ['name' => 'Korea', 'flag' => '🇰🇷'],
            'LB' => ['name' => 'Lebanon', 'flag' => '🇱🇧'],
            'MY' => ['name' => 'Malaysia', 'flag' => '🇲🇾'],
            'MX' => ['name' => 'Mexico', 'flag' => '🇲🇽'],
            'NL' => ['name' => 'Netherlands', 'flag' => '🇳🇱'],
            'NZ' => ['name' => 'New Zealand', 'flag' => '🇳🇿'],
            'NG' => ['name' => 'Nigeria', 'flag' => '🇳🇬'],
            'NO' => ['name' => 'Norway', 'flag' => '🇳🇴'],
            'PK' => ['name' => 'Pakistan', 'flag' => '🇵🇰'],
            'PE' => ['name' => 'Peru', 'flag' => '🇵🇪'],
            'PH' => ['name' => 'Philippines', 'flag' => '🇵🇭'],
            'PL' => ['name' => 'Poland', 'flag' => '🇵🇱'],
            'PT' => ['name' => 'Portugal', 'flag' => '🇵🇹'],
            'RO' => ['name' => 'Romania', 'flag' => '🇷🇴'],
            'RU' => ['name' => 'Russia', 'flag' => '🇷🇺'],
            'SA' => ['name' => 'Saudi Arabia', 'flag' => '🇸🇦'],
            'ZA' => ['name' => 'South Africa', 'flag' => '🇿🇦'],
            'ES' => ['name' => 'Spain', 'flag' => '🇪🇸'],
            'LK' => ['name' => 'Sri Lanka', 'flag' => '🇱🇰'],
            'SE' => ['name' => 'Sweden', 'flag' => '🇸🇪'],
            'CH' => ['name' => 'Switzerland', 'flag' => '🇨🇭'],
            'TH' => ['name' => 'Thailand', 'flag' => '🇹🇭'],
            'TN' => ['name' => 'Tunisia', 'flag' => '🇹🇳'],
            'TR' => ['name' => 'Turkey', 'flag' => '🇹🇷'],
            'UA' => ['name' => 'Ukraine', 'flag' => '🇺🇦'],
            'AE' => ['name' => 'United Arab Emirates', 'flag' => '🇦🇪'],
            'GB' => ['name' => 'United Kingdom', 'flag' => '🇬🇧'],
            'US' => ['name' => 'United States', 'flag' => '🇺🇸'],
            'VN' => ['name' => 'Vietnam', 'flag' => '🇻🇳'],
            'ZW' => ['name' => 'Zimbabwe', 'flag' => '🇿🇼'],
        ];
    }

    /** code => plain name (for the searchable admin select). */
    public static function names(): array
    {
        return array_map(fn (array $c): string => $c['name'], self::all());
    }

    /** code => "🇦🇺 Australia" (for the flag-prefixed portal/claim selects). */
    public static function withFlags(): array
    {
        $out = [];

        foreach (self::all() as $code => $c) {
            $out[$code] = trim(($c['flag'] ? $c['flag'] . ' ' : '') . $c['name']);
        }

        return $out;
    }
}
