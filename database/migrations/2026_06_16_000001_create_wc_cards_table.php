<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_cards', function (Blueprint $table) {
            $table->bigIncrements('cardID');
            $table->unsignedBigInteger('fixtureID');
            $table->unsignedBigInteger('playerID');
            $table->unsignedBigInteger('teamID');
            $table->string('type', 10); // 'yellow' or 'red'
            // true when a 2nd yellow results in a red — counts as red only.
            $table->boolean('is_second_yellow')->default(false);
            $table->timestamps();

            $table->foreign('fixtureID')->references('fixtureID')->on('wc_fixtures')->cascadeOnDelete();
            $table->foreign('playerID')->references('playerID')->on('wc_players')->cascadeOnDelete();
            $table->foreign('teamID')->references('teamID')->on('wc_teams');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_cards');
    }
};
