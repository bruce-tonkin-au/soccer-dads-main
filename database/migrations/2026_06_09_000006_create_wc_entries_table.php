<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_entries', function (Blueprint $table) {
            $table->bigIncrements('entryID');
            // members.memberID is a plain integer identity column, so the FK column must match.
            $table->integer('memberID');
            $table->unsignedBigInteger('orderID')->nullable();
            $table->string('entry_name', 100);
            $table->boolean('draw_completed')->default(false);
            $table->timestamps();

            $table->foreign('memberID')->references('memberID')->on('members')->cascadeOnDelete();
            $table->foreign('orderID')->references('orderID')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_entries');
    }
};
