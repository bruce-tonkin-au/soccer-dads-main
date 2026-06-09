<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_entry_players', function (Blueprint $table) {
            $table->bigIncrements('entryPlayerID');
            $table->unsignedBigInteger('entryID');
            $table->unsignedBigInteger('playerID');
            $table->smallInteger('slot');

            $table->foreign('entryID')->references('entryID')->on('wc_entries')->cascadeOnDelete();
            $table->foreign('playerID')->references('playerID')->on('wc_players')->cascadeOnDelete();

            $table->unique(['entryID', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_entry_players');
    }
};
