<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-member access to a night. Two flags:
     *   allowed = 1  → admin has granted this member access to the night
     *   hidden  = 0  → the member has NOT hidden it from their own view
     * A night shows for a member when allowed = 1 AND hidden = 0; registration for
     * a night requires it to be visible to them. Integer FKs to match the legacy
     * integer PKs. Unique (memberID, nightID) so the backfill can insertOrIgnore.
     */
    public function up(): void
    {
        Schema::create('member_nights', function (Blueprint $table) {
            $table->integer('memberID');
            $table->integer('nightID');
            $table->integer('allowed')->default(1);
            $table->integer('hidden')->default(0);

            $table->foreign('memberID')
                ->references('memberID')->on('members')
                ->cascadeOnDelete();
            $table->foreign('nightID')
                ->references('nightID')->on('nights')
                ->cascadeOnDelete();

            $table->unique(['memberID', 'nightID']);
            $table->index('nightID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_nights');
    }
};
