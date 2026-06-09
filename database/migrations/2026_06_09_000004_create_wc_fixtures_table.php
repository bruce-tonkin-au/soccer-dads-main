<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_fixtures', function (Blueprint $table) {
            $table->bigIncrements('fixtureID');
            $table->string('stage', 20)->default('group');
            $table->char('group_letter', 1)->nullable();
            $table->unsignedBigInteger('home_team_id')->nullable();
            $table->unsignedBigInteger('away_team_id')->nullable();
            $table->string('home_placeholder', 100)->nullable();
            $table->string('away_placeholder', 100)->nullable();
            $table->timestampTz('match_datetime')->nullable();
            $table->string('venue', 150)->nullable();
            $table->smallInteger('home_score')->nullable();
            $table->smallInteger('away_score')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();

            $table->foreign('home_team_id')->references('teamID')->on('wc_teams')->nullOnDelete();
            $table->foreign('away_team_id')->references('teamID')->on('wc_teams')->nullOnDelete();

            $table->index(['stage', 'status']);
            $table->index('match_datetime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_fixtures');
    }
};
