<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('memberSlug')->nullable()->after('memberCode');
        });

        // Generate slugs for all existing members
        $members = DB::table('members')->orderBy('memberID')->get();
        $usedSlugs = [];

        foreach ($members as $member) {
            $name = trim($member->memberNameFirst . ' ' . $member->memberNameLast);
            $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
            $base = trim($base, '-');

            if (empty($base)) {
                $base = 'player-' . $member->memberID;
            }

            $slug = $base;
            $counter = 2;

            while (in_array($slug, $usedSlugs)) {
                $slug = $base . '-' . $counter;
                $counter++;
            }

            $usedSlugs[] = $slug;

            DB::table('members')
                ->where('memberID', $member->memberID)
                ->update(['memberSlug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('memberSlug');
        });
    }
};