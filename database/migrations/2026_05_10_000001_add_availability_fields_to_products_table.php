<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dateTime('productAvailableFrom')->nullable()->after('productActive');
            $table->dateTime('productAvailableTo')->nullable()->after('productAvailableFrom');
            $table->integer('productMaxQuantity')->default(1)->after('productAvailableTo');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['productAvailableFrom', 'productAvailableTo', 'productMaxQuantity']);
        });
    }
};
