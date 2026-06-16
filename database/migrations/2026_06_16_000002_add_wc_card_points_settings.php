<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ([
            ['key' => 'points_yellow_card', 'value' => '1', 'label' => 'Points awarded per yellow card'],
            ['key' => 'points_red_card', 'value' => '3', 'label' => 'Points awarded per red card'],
        ] as $row) {
            if (! DB::table('wc_settings')->where('key', $row['key'])->exists()) {
                DB::table('wc_settings')->insert([
                    'key'        => $row['key'],
                    'value'      => $row['value'],
                    'label'      => $row['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('wc_settings')
            ->whereIn('key', ['points_yellow_card', 'points_red_card'])
            ->delete();
    }
};
