<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'memberClaimed')) {
                $table->boolean('memberClaimed')->default(false)->after('memberRegisteredDate');
            }
            if (!Schema::hasColumn('members', 'memberClaimedAt')) {
                $table->timestamp('memberClaimedAt')->nullable()->after('memberClaimed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['memberClaimed', 'memberClaimedAt']);
        });
    }
};
