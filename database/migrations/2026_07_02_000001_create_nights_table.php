<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "night" concept: a recurring game night at a venue (e.g. Friday, Tuesday).
     * Legacy-style table to sit beside seasons/members — custom night* column names,
     * integer identity PK (matches the integer FKs elsewhere, NOT bigint), and its
     * own created/edited timestamp columns rather than Laravel timestamps.
     */
    public function up(): void
    {
        Schema::create('nights', function (Blueprint $table) {
            $table->increments('nightID');           // Postgres integer identity PK
            $table->text('nightName');               // section label, e.g. "Friday"
            $table->text('nightVenue');              // venue label shown beside the name
            $table->text('nightAddress')->nullable();
            $table->integer('nightDayOfWeek')->nullable(); // 1-7, optional convenience
            $table->integer('nightActive')->default(1);
            $table->integer('nightSort')->default(0);
            $table->timestamp('nightCreated')->useCurrent();
            $table->timestamp('nightEdited')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nights');
    }
};
