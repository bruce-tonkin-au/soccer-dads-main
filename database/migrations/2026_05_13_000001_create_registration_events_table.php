<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game-registration-events', function (Blueprint $table) {
            $table->id('eventID');
            $table->integer('gameID');
            $table->integer('memberID');
            // registered, deregistered, bench_added, bench_promoted, admin_registered, admin_deregistered
            $table->string('eventType', 30);
            // Active-player sequence number at time of this event (null for deregister/bench events)
            $table->integer('registrationSequence')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('gameID');
            $table->index(['gameID', 'memberID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game-registration-events');
    }
};
