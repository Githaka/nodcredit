<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToInvestmentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->primary('id');
        });

        Schema::table('investment_partial_liquidations', function (Blueprint $table) {
            $table->primary('id');
            $table->index('investment_id', 'index_investment_id');
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
            $table->dropPrimary('id');
        });

        Schema::table('investment_partial_liquidations', function (Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropIndex('index_investment_id');
        });
    }
}
