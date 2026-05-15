<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('orderName')->nullable()->after('orderNotes');
            $table->string('orderEmail')->nullable()->after('orderName');
            $table->string('orderPhone')->nullable()->after('orderEmail');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['orderName', 'orderEmail', 'orderPhone']);
        });
    }
};
