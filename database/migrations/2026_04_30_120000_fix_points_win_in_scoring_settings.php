<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('scoring-settings')
            ->where('settingsPointsWin', 3)
            ->update(['settingsPointsWin' => 2]);
    }

    public function down(): void
    {
        DB::table('scoring-settings')
            ->where('settingsPointsWin', 2)
            ->update(['settingsPointsWin' => 3]);
    }
};
