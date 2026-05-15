<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // memberKey → current PostgreSQL memberID
    // Resolved from members.csv (336 records, superset of the April SQL dump).
    // Members with emails were matched by email; name-only members by full name.
    private array $keyToMemberID = [
        '32yM46I7AmNe' => 145,  // Jake Howe           [email]
        '9vz65g7WIsSl' => 146,  // Zac Ferguson        [email]
        'GsPVU597tmYx' => 149,  // Brent Prior         [email]
        'Wtu6J1ScI8fk' => 148,  // Diederik Polman     [email]
        'BIkdJ1iUmnut' => 154,  // Brad Bateman        [name]
        'a7khn4biKf9J' => 157,  // Neil Slater         [email]
        's8SX7nBbpNwr' => 155,  // Harry Saleeba       [email]
        'P1t2s3OFbG70' => 161,  // Paul Morton         [email]
        'ag34SKh810BR' => 173,  // Adam Cartland       [email]
        '5La7Ivx8rwg9' => 150,  // Thomas Morgan       [email]
        'x4QWUoKAby76' => 186,  // Nick Betts          [email]
        'n2p5sz91oZQM' => 198,  // Luigi Falivene      [email]
        'D4yntrh5zW6U' => 174,  // Richard Guzman      [email]
        'WS653rX9f0os' => 172,  // Jett Tonkin         [email]
        'ckUzHEfrm4xX' => 202,  // Luke Allen          [email]
        'Tiav9bJ0EeNL' => 219,  // Milos Castelli      [email]
        'mePOB7AbEdNr' => 229,  // Neil Archer         [email]
        'XcF6d3SjFZZm' => 232,  // Liam Betts          [name]
        'LEFw30zYGSis' => 240,  // Luke Clayton        [email]
        'VmGIoiz5qZH6' => 215,  // Michael Cahill      [email]
        'pt9BhhEoXzNh' => 238,  // Isaac Stanford      [email]
        'vzPDC6NHDhrS' => 249,  // Kevin Murphy        [email]
        'UxPDmQcCvc8D' => 245,  // Sam Rogers          [email]
        'L67kPtEHdxeA' => 257,  // Hamish Dunsford     [email]
        '4d7YKw55YB5J' => 261,  // Arie Kristian       [email]
        'b8QXbnJ3JR8p' => 239,  // Michael Pavy        [name]
        'tejvAGx7ByHV' => 267,  // Paul Cridge         [email]
        'eB94z52TKCh7' => 275,  // David Fessler       [email]
        'lqbtZ78hXJKD' => 213,  // Anthony Guzman      [email]
        'UUjxS9v2RzxW' => 283,  // Obinna Nduka        [name]
        '2hZpczhwhWp2' => 278,  // Werner Bosch        [email]
        'whTNRnEjrtPK' => 276,  // John Lawler         [email]
        '7fOIgKDo4G9N' => 289,  // David Wilde         [email]
        'ZptiyaPOLoi4' => 290,  // Charlie Ford        [email]
        'fjyTDb9262za' => 285,  // Marcus Liew         [email]
        '9Y1c8l4eZS1t' => 301,  // Matthew Robinson    [name]
        'gd5xKrFmeKue' => 299,  // Peter Anton Lin     [email]
        'oVH2nfw1C2uh' => 309,  // Chris Williams      [email]
        'AzW09vVDeg3E' => 304,  // Greg Parkes         [name]
        'VE9yaqkTR3sz' => 325,  // Michael Ziersch     [email]
        'oy48XZmUm7Xx' => 334,  // Roland Korkomaz     [name]
    ];

    public function up(): void
    {
        $updated = 0;

        foreach ($this->keyToMemberID as $key => $memberID) {
            $rows = DB::table('games')
                ->where('gameBibs', $key)
                ->whereNull('gameBibsMemberID')
                ->count();

            if ($rows > 0) {
                DB::table('games')
                    ->where('gameBibs', $key)
                    ->whereNull('gameBibsMemberID')
                    ->update(['gameBibsMemberID' => $memberID]);
                $updated += $rows;
            }
        }

        echo "\n=== gameBibsMemberID backfill (CSV pass) ===\n";
        echo "Additional records migrated: {$updated}\n";
        echo "All gameBibs records should now be fully resolved.\n";
        echo "============================================\n";
    }

    public function down(): void
    {
        foreach ($this->keyToMemberID as $key => $memberID) {
            DB::table('games')
                ->where('gameBibs', $key)
                ->where('gameBibsMemberID', $memberID)
                ->update(['gameBibsMemberID' => null]);
        }
    }
};
