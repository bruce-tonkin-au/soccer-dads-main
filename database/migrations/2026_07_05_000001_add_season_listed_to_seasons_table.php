<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether a season shows on the PUBLIC /seasons list — separate from
     * seasonVisible (which gates registration/visibility). A season can be
     * registerable-but-unlisted. Nullable integer default 1 to match the
     * legacy-style flag columns (seasonVisible etc.). Mirrors the nightID
     * migration pattern (Schema::table, ->after an existing column).
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->integer('seasonListed')->nullable()->default(1)->after('seasonVisible');
        });

        // Preserve current behaviour exactly: what is visible today stays listed.
        DB::table('seasons')->update(['seasonListed' => DB::raw('"seasonVisible"')]);
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('seasonListed');
        });
    }
};
