<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        do {
            $code = strtoupper(Str::random(4));
        } while (DB::table('games')->where('gameCode', $code)->exists());

        DB::table('games')->where('gameID', 583)->update(['gameCode' => $code]);
    }

    public function down(): void
    {
        DB::table('games')->where('gameID', 583)->update(['gameCode' => null]);
    }
};
