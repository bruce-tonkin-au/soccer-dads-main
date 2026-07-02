<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill so the app behaves EXACTLY as today: one night (Friday), every
     * visible season pointed at it, every active member granted access. Nothing
     * member-facing reads these tables yet, so this changes no behaviour.
     *
     * Re-runnable-safe:
     *   - Friday night is found-or-created (never duplicated).
     *   - Seasons are only filled where nightID IS NULL (won't stomp a season
     *     later pointed at another night).
     *   - member_nights uses insertOrIgnore against the unique (memberID, nightID)
     *     index (no double inserts, won't reset a member's own hidden flag).
     */
    public function up(): void
    {
        // 1. Find-or-create the Friday night. Placeholder venue — edit in the UI later.
        $friday = DB::table('nights')->where('nightName', 'Friday')->first();

        if ($friday) {
            $fridayID = $friday->nightID;
        } else {
            $fridayID = DB::table('nights')->insertGetId([
                'nightName'   => 'Friday',
                'nightVenue'  => 'Friday venue',
                'nightActive' => 1,
                'nightSort'   => 0,
                'nightCreated' => now(),
                'nightEdited'  => now(),
            ], 'nightID');
        }

        // 2. Point every currently-visible season with no night yet at Friday.
        //    (Historical/hidden seasons stay null — harmless.)
        DB::table('seasons')
            ->where('seasonVisible', 1)
            ->whereNull('nightID')
            ->update(['nightID' => $fridayID]);

        // 3. Grant every active member access to Friday (allowed=1, hidden=0),
        //    skipping any row that already exists.
        $rows = DB::table('members')
            ->where('memberActive', 1)
            ->pluck('memberID')
            ->map(fn ($memberID) => [
                'memberID' => $memberID,
                'nightID'  => $fridayID,
                'allowed'  => 1,
                'hidden'   => 0,
            ])
            ->all();

        if (! empty($rows)) {
            DB::table('member_nights')->insertOrIgnore($rows);
        }
    }

    public function down(): void
    {
        $friday = DB::table('nights')->where('nightName', 'Friday')->first();

        if (! $friday) {
            return;
        }

        // Unhook seasons and remove the Friday grants, then the night itself.
        DB::table('seasons')->where('nightID', $friday->nightID)->update(['nightID' => null]);
        DB::table('member_nights')->where('nightID', $friday->nightID)->delete();
        DB::table('nights')->where('nightID', $friday->nightID)->delete();
    }
};
