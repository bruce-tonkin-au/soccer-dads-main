<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_goals', function (Blueprint $table) {
            $table->bigIncrements('goalID');
            $table->unsignedBigInteger('fixtureID');
            $table->unsignedBigInteger('playerID');
            $table->unsignedBigInteger('teamID');
            $table->smallInteger('minute')->nullable();
            $table->boolean('is_own_goal')->default(false);
            $table->timestamps();

            $table->foreign('fixtureID')->references('fixtureID')->on('wc_fixtures')->cascadeOnDelete();
            $table->foreign('playerID')->references('playerID')->on('wc_players')->cascadeOnDelete();
            $table->foreign('teamID')->references('teamID')->on('wc_teams');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_goals');
    }
};
