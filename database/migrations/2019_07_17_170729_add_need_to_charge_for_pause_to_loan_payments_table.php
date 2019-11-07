<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNeedToChargeForPauseToLoanPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->boolean('need_to_charge_for_pause')->default(false);
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
            $table->dropColumn('need_to_charge_for_pause');
        });
    }
}
