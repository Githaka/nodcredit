<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesInInvestmentPartialLiquidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investment_partial_liquidations', function (Blueprint $table) {
            $table->dropColumn('user_profit');
            $table->renameColumn('profit_penalty_amount', 'penalty_amount');
            $table->renameColumn('profit_penalty_percent', 'penalty_percent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('investment_partial_liquidations', function (Blueprint $table) {
            $table->decimal('user_profit', 12, 2);
            $table->renameColumn('penalty_amount', 'profit_penalty_amount');
            $table->renameColumn('penalty_percent', 'profit_penalty_percent');
        });
    }
}
