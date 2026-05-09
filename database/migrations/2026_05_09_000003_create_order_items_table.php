<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('itemID');
            $table->unsignedBigInteger('orderID')->index();
            $table->unsignedBigInteger('productID')->index();
            $table->integer('itemQuantity');
            $table->decimal('itemPrice', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
