<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('productID');
            $table->string('productName');
            $table->text('productDescription')->nullable();
            $table->decimal('productPrice', 8, 2);
            $table->string('productImage')->nullable();
            $table->integer('productStock')->default(0);
            $table->boolean('productActive')->default(true);
            $table->string('productSlug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
