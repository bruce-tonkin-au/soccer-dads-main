<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player-ratings', function (Blueprint $table) {
            $table->id('ratingID');
            $table->integer('raterMemberID');
            $table->integer('ratedMemberID');
            $table->tinyInteger('ratingGoal')->default(0);
            $table->tinyInteger('ratingPassing')->default(0);
            $table->tinyInteger('ratingWork')->default(0);
            $table->tinyInteger('ratingDefending')->default(0);
            $table->tinyInteger('ratingOverall')->default(0);
            $table->timestamps();
            $table->unique(['raterMemberID', 'ratedMemberID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player-ratings');
    }
};
