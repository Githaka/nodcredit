<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestmentProfitPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investment_profit_payments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('investment_id');
            $table->decimal('amount', 12, 2);
            $table->decimal('liquidations_profit', 12, 2);
            $table->string('status', 30);
            $table->integer('period_days');
            $table->dateTime('period_start');
            $table->dateTime('period_end');
            $table->dateTime('scheduled_at');
            $table->boolean('auto_payout')->default(false);
            $table->dateTime('paid_out_at')->nullable()->default(null);
            $table->text('payout_response')->nullable()->default(null);
            $table->timestamps();

            $table->primary('id');
            $table->index('investment_id', 'index_investment_id');
            $table->index('auto_payout', 'index_auto_payout');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('investment_profit_payments');
    }
}
