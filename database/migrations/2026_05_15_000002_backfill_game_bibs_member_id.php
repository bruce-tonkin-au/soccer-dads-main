<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // memberKey (old MySQL column, dropped in PG migration) → memberID
    // Extracted from the April 2026 members.sql dump.
    private array $keyToMemberID = [
        'B4U3e5o6K8Nb' => 1,
        'Ton659Odr836' => 2,
        'wS85OGeN3D0f' => 3,
        'P2Lr4mcJ1gKR' => 4,
        'SAWuXB814gK7' => 5,
        'nwFm379guES1' => 6,
        'igvL8VzosKFn' => 7,
        'JP1wv4m5Q6rS' => 8,
        'mKc3IZ7VEat5' => 9,
        'LJ6aVuWX4sc5' => 10,
        'Zx9BPslSrCMX' => 11,
        '1703hoAt5j4a' => 12,
        'nUX4rM2cQt6p' => 13,
        '2rUB0cKNYw37' => 14,
        'u9jLo7HNmQ2R' => 15,
        'eONH19zJiKft' => 16,
        'khzB4Uu9F2Aw' => 17,
        'u1bAl4HDdQhy' => 18,
        '39cVlp7tug2m' => 19,
        '20Ja6xQORW8d' => 20,
        'g1K2ucnbkfqI' => 21,
        'a34Kzhf7BrmG' => 22,
        'fgaDXCol1N56' => 23,
        'CbNv42Vznh0F' => 24,
        'Uh2HQSxcP5NM' => 25,
        'wcGkJ6DvK31T' => 26,
        'lbg362nfeZCK' => 27,
        'h40sT2A8Lk3r' => 28,
        '1t3OwmqXC25D' => 29,
        'q8o9aUkjdBVP' => 31,
        '9CL84hspoury' => 32,
        'jb346ZiyzBnA' => 33,
        '00LoFBI8w4p2' => 34,
        'p735ym1gFqPD' => 35,
        'Qa7sLcB3IAEm' => 36,
        'ECc928eqL4I1' => 37,
        'AUfc61np5t4I' => 38,
        '3Nsm4eyoWv8A' => 39,
        'SeVd3AZq0IkX' => 40,
        'EPY62FKJbqV3' => 41,
        '568KTkNDZYGx' => 42,
        'LwaV4Zmd8W5f' => 43,
        '10efREwjut4M' => 44,
        'aolNtT4ieY53' => 45,
        'P6AiLuUBD394' => 46,
        '6dSV2INRpKAM' => 47,
        'bHANwZ37aPj1' => 48,
        '6Kh7d8lv91Ww' => 49,
        'HmPeCLThzk2r' => 50,
        'cHD1yEtG24m5' => 51,
        'hiH3X2DPxwfV' => 52,
        'wMVPWoKQ7a0N' => 53,
        '2e3bwRXvCJra' => 54,
        'jrbPuKdCNifE' => 55,
        'R6mGsfDo8rlS' => 56,
        '1hE8nYrDQWA5' => 57,
        'b9tRek0upAC7' => 58,
        'lc1wBbDFq9Hx' => 59,
        '496MaT624RoY' => 60,
        'JER734emy376' => 61,
        'ehdy2093URKI' => 62,
        'AH28EJ85ry78' => 63,
        'dj89EJ72RH46' => 64,
        'ey78dJ67Eu56' => 65,
        '4785EJkc2893' => 66,
        '10sFgX9OSPa2' => 67,
        'D6oiBfpy5aW0' => 68,
        '296yHcdrla78' => 69,
        'EUR989478UUJ' => 70,
        'kO92y6G3sxi7' => 71,
        '09M61NC2akrL' => 72,
        'YsF7N4hz2XiW' => 73,
        'q9O7vbLX026p' => 74,
        'x67wcCPORi1T' => 75,
        'PCgbk8uUFNzm' => 76,
        'xdmvBh7qiFNb' => 77,
        'rOv0R9Nb1BQf' => 78,
        'r73KhP4l8Ev6' => 79,
        'BDiz51O0dLr9' => 80,
        'QNB1pIAj3wLq' => 81,
        '4n95VbFgGzAR' => 82,
        'yPdRp36KUDS7' => 83,
        'a572T190zFXt' => 84,
        'WSzTIF923aMC' => 85,
        'oN3t4KPRe7lx' => 86,
        'Bw45qQXyAsfx' => 87,
        'IDeKiT7f9l6N' => 88,
        'wgo06HKBN7f9' => 89,
        'ELw20Klp17sx' => 90,
        'EbuAg6dmjRJI' => 91,
        '8G5WnXkxF9ry' => 92,
        'S846Zfcs0K5l' => 93,
        '50NgKOSzw9cX' => 94,
        'CovVygFXrwHR' => 95,
        'wvebKnM6kOAc' => 96,
        'Q5POGNL4t9w1' => 97,
        '0T2Oh81lP5dA' => 98,
        'M78aitb16kDT' => 99,
        'l093Lu1RN2cV' => 100,
        'V8930va6nqSk' => 101,
        'UEeLHW0Rqtc5' => 102,
        'FlCk48h7xuUH' => 103,
        'TsNPCaA394j1' => 104,
        'lCUi67Ru3DeV' => 105,
        'kD3fwWvgaA9u' => 106,
        'cXn4fNrtD1Yk' => 107,
        'g480lvbkGw71' => 108,
        'oDmqr2051iz6' => 109,
        'gOcaBis3126F' => 110,
        '2mkFy6oSv14N' => 111,
        'aXJ06IVWfDtC' => 112,
        'Ph4E1bHj65gF' => 113,
        'U2w7ljTt4SLg' => 114,
        '8WDsET59Z7Qo' => 115,
        'TwEsyj3mhfAX' => 116,
        'T7Z0V64e1hS9' => 117,
        '3U4KNMj1ZGwA' => 118,
        '004bDqIWjnNs' => 119,
        'm6fQpYM38Z74' => 120,
        'GHWcKJCSk8FM' => 121,
        'z3Gy2M8g1Z0u' => 122,
        'N9s82bL4jgh1' => 123,
        'XaqFTl7G86Mg' => 124,
        '9Iv0d5uE6jl7' => 125,
        'F4CkZLaoUD3r' => 126,
        'ufnXRxUWFYHD' => 127,
        '6PWBV1ev8n2U' => 128,
        'W8H0R52wnD4B' => 129,
        'D5Wp6eGmB8ok' => 130,
        '0W9J8U3V2Mr6' => 131,
        'a867cT53HF9b' => 132,
        'P1u0qZehbSsW' => 133,
        '82V9XB1I4y7w' => 134,
        'Ew2vx61ZreT8' => 135,
        '9IdeBRC20xfT' => 136,
        'TU8AtP9sp4ya' => 138,
        '7E4XC5JWzFhx' => 139,
        'o7tiV6gs85T9' => 140,
        'YavZ73o02r1c' => 141,
        'ka1hS93y6ACL' => 142,
        '92IqEOPV7xyn' => 143,
        '326zdPY71n5l' => 144,
    ];

    public function up(): void
    {
        $updated   = 0;
        $skipped   = 0;
        $unmatched = [];

        $games = DB::table('games')
            ->whereNotNull('gameBibs')
            ->where('gameBibs', '!=', '')
            ->whereNull('gameBibsMemberID')
            ->select('gameID', 'gameBibs')
            ->get();

        foreach ($games as $game) {
            $key = $game->gameBibs;
            if (isset($this->keyToMemberID[$key])) {
                DB::table('games')
                    ->where('gameID', $game->gameID)
                    ->update(['gameBibsMemberID' => $this->keyToMemberID[$key]]);
                $updated++;
            } else {
                $unmatched[$key] = ($unmatched[$key] ?? 0) + 1;
                $skipped++;
            }
        }

        $unmatchedSummary = implode(', ', array_map(
            fn($k, $c) => "{$k} ({$c})",
            array_keys($unmatched),
            array_values($unmatched)
        ));

        echo "\n=== gameBibsMemberID backfill ===\n";
        echo "Migrated:  {$updated} games\n";
        echo "Unmatched: {$skipped} games (former players not in members dump)\n";
        if ($unmatched) {
            echo "Keys needing manual assignment:\n";
            foreach ($unmatched as $key => $count) {
                echo "  {$key}  ({$count} games)\n";
            }
        }
        echo "=================================\n";
    }

    public function down(): void
    {
        // Clear only the values we set (leave any manually-assigned ones intact
        // by only clearing rows where gameBibs still holds a key from our mapping).
        foreach (array_keys($this->keyToMemberID) as $key) {
            DB::table('games')
                ->where('gameBibs', $key)
                ->whereIn('gameBibsMemberID', array_values($this->keyToMemberID))
                ->update(['gameBibsMemberID' => null]);
        }
    }
};
