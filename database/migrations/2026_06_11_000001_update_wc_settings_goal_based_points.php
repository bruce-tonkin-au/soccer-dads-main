<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Win/draw scoring is removed — points are now purely goal-based.
        DB::table('wc_settings')
            ->whereIn('key', ['points_team_win', 'points_team_draw'])
            ->delete();

        // Every goal scored by an assigned player is worth 1 point.
        DB::table('wc_settings')
            ->where('key', 'points_player_goal')
            ->update([
                'value'      => '1',
                'label'      => 'Points awarded per player goal',
                'updated_at' => $now,
            ]);

        // New: every goal scored by an assigned team is worth 1 point.
        if (! DB::table('wc_settings')->where('key', 'points_team_goal')->exists()) {
            DB::table('wc_settings')->insert([
                'key'        => 'points_team_goal',
                'value'      => '1',
                'label'      => 'Points awarded per team goal',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $now = now();

        DB::table('wc_settings')->where('key', 'points_team_goal')->delete();

        DB::table('wc_settings')
            ->where('key', 'points_player_goal')
            ->update([
                'value'      => '2',
                'label'      => 'Points awarded per player goal',
                'updated_at' => $now,
            ]);

        foreach ([
            ['key' => 'points_team_win', 'value' => '3', 'label' => 'Points awarded for a team win'],
            ['key' => 'points_team_draw', 'value' => '1', 'label' => 'Points awarded for a team draw'],
        ] as $row) {
            DB::table('wc_settings')->updateOrInsert(
                ['key' => $row['key']],
                ['value' => $row['value'], 'label' => $row['label'], 'created_at' => $now, 'updated_at' => $now],
            );
        }
    }
};
