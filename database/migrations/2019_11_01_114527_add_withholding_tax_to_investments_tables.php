<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWithholdingTaxToInvestmentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->integer('withholding_tax_percent', false, true)->default(0);
        });

        Schema::table('investment_profit_payments', function (Blueprint $table) {
            $table->decimal('payout_amount', 12, 2)->default(0);
            $table->decimal('withholding_tax_amount', 12, 2)->default(0);
            $table->integer('withholding_tax_percent')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn('withholding_tax_percent');
        });

        Schema::table('investment_profit_payments', function (Blueprint $table) {
            $table->dropColumn('payout_amount');
            $table->dropColumn('withholding_tax_amount');
            $table->dropColumn('withholding_tax_percent');
        });
    }
}
