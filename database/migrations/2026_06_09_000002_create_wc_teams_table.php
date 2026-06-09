<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_teams', function (Blueprint $table) {
            $table->bigIncrements('teamID');
            $table->string('name');
            $table->string('code', 10);
            $table->string('flag', 20)->nullable();
            $table->string('confederation');
            $table->integer('fifa_ranking')->nullable();
            $table->smallInteger('pot');
            $table->smallInteger('seed_tier');
            $table->char('group_letter', 1)->nullable();
            $table->smallInteger('group_position')->nullable();
            $table->boolean('qualified')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_teams');
    }
};
