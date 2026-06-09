<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_settings', function (Blueprint $table) {
            $table->bigIncrements('settingID');
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->string('label');
            $table->timestamps();
        });

        $now = now();

        DB::table('wc_settings')->insert([
            [
                'key'        => 'points_team_win',
                'value'      => '3',
                'label'      => 'Points awarded for a team win',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'points_team_draw',
                'value'      => '1',
                'label'      => 'Points awarded for a team draw',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'points_player_goal',
                'value'      => '2',
                'label'      => 'Points awarded per player goal',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'draw_locked',
                'value'      => 'false',
                'label'      => 'Whether the sweepstake draw is locked',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'draw_run_at',
                'value'      => null,
                'label'      => 'Timestamp the draw was run',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_settings');
    }
};
