<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_players', function (Blueprint $table) {
            $table->bigIncrements('playerID');
            $table->unsignedBigInteger('teamID');
            $table->string('name');
            $table->string('position')->nullable();
            $table->smallInteger('shirt_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('teamID')->references('teamID')->on('wc_teams');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_players');
    }
};
