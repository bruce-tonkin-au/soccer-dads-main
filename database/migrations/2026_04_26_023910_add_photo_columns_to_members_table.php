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
        Schema::table('members', function (Blueprint $table) {
            $table->string('memberPhoto')->nullable()->after('memberSlug');
            $table->string('memberPhotoCard')->nullable()->after('memberPhoto');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['memberPhoto', 'memberPhotoCard']);
        });
    }
};
