<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'memberCountry')) {
                $table->string('memberCountry')->nullable()->default('AU')->change();
            } else {
                $table->string('memberCountry')->nullable()->default('AU')->after('memberPhoneMobile');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('memberCountry');
        });
    }
};
