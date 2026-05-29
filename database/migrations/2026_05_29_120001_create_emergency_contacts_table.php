<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id('contactID');
            $table->unsignedBigInteger('memberID')->index();
            $table->string('contactName');
            $table->string('contactRelationship')->nullable();
            $table->string('contactPhone');
            $table->string('contactEmail')->nullable();
            $table->boolean('contactPrimary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
