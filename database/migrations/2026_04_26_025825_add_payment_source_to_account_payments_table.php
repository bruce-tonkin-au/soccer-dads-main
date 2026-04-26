<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account-payments', function (Blueprint $table) {
            $table->string('paymentSource')->nullable()->after('paymentVisible');
        });

        DB::statement("UPDATE `account-payments` SET paymentSource = CASE
            WHEN formPaymentID IS NOT NULL AND formPaymentID != '' THEN 'stripe'
            WHEN formPaymentType = 2 THEN 'cash'
            ELSE 'other'
        END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account-payments', function (Blueprint $table) {
            $table->dropColumn('paymentSource');
        });
    }
};
