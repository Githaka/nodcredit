<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMonthToLoanPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->unsignedInteger('payment_month')->default(0);
            $table->boolean('is_drafted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_month', 'is_drafted']);
        });
    }
}
