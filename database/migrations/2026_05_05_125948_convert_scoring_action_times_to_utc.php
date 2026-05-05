<?php

use Illuminate\Database\Migrations\Migration;

// No-op: actionTime in scoring-actions is already stored consistently with
// gameYouTubeStart (both Adelaide local on the MySQL server). Conversion is
// not needed until the database is switched to PostgreSQL.
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
