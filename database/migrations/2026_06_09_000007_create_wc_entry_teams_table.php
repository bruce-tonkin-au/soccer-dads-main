<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_entry_teams', function (Blueprint $table) {
            $table->bigIncrements('entryTeamID');
            $table->unsignedBigInteger('entryID');
            $table->unsignedBigInteger('teamID');
            $table->smallInteger('tier');

            $table->foreign('entryID')->references('entryID')->on('wc_entries')->cascadeOnDelete();
            $table->foreign('teamID')->references('teamID')->on('wc_teams')->cascadeOnDelete();

            $table->unique(['entryID', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_entry_teams');
    }
};
