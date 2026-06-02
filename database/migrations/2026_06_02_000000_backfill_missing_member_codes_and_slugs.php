<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Support\MemberIdentifier;

return new class extends Migration
{
    /**
     * Backfill memberCode/memberSlug for any member that is missing them.
     *
     * A handful of records (e.g. blank test entries) were created without a
     * code or slug being generated, which breaks the URLs that rely on them.
     * Generate the missing identifiers using the same logic as player
     * creation so they are unique and consistent.
     */
    public function up(): void
    {
        $members = DB::table('members')
            ->where(function ($q) {
                $q->whereNull('memberCode')->orWhere('memberCode', '');
            })
            ->orWhere(function ($q) {
                $q->whereNull('memberSlug')->orWhere('memberSlug', '');
            })
            ->orderBy('memberID')
            ->get();

        foreach ($members as $member) {
            $update = [];

            if (empty($member->memberCode)) {
                $update['memberCode'] = MemberIdentifier::generateCode();
            }

            if (empty($member->memberSlug)) {
                $update['memberSlug'] = MemberIdentifier::generateSlug(
                    $member->memberNameFirst,
                    $member->memberNameLast,
                    (int) $member->memberID
                );
            }

            if ($update) {
                DB::table('members')->where('memberID', $member->memberID)->update($update);
            }
        }
    }

    public function down(): void
    {
        // Data backfill only — generated identifiers are left in place.
    }
};
