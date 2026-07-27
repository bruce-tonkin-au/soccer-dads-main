<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('newsID');
            $table->string('newsTitle', 255);
            $table->text('newsBody');
            $table->date('newsDate');
            $table->integer('newsActive')->default(1);
            $table->timestamps();
        });

        // Three dummy items to launch with — edit or delete via /manage.
        $now = now();
        DB::table('news')->insert([
            [
                'newsTitle' => 'Season 3 is under way',
                'newsBody' => '<p>Friday and Tuesday nights are rolling for Season 3, 2026. Grab your spot each week through your registration link — first night free, then $10 a night from your ledger.</p>',
                'newsDate' => $now->copy()->subDays(2)->toDateString(),
                'newsActive' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'newsTitle' => 'Live commentary on game nights',
                'newsBody' => '<p>Our courtside commentary now kicks off each round and wraps up the night — goals, milestones and the odd bit of cheek, straight through the speaker.</p>',
                'newsDate' => $now->copy()->subDays(5)->toDateString(),
                'newsActive' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'newsTitle' => 'World Cup 2026 sweepstake ladder is live',
                'newsBody' => '<p>Follow your teams and players through the tournament on the live ladder — head to the World Cup page to see where you sit.</p>',
                'newsDate' => $now->copy()->subDays(9)->toDateString(),
                'newsActive' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
