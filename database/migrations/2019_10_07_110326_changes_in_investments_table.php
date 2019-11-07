<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesInInvestmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->string('profit_payout_type')->default('single');
            $table->dateTime('maturity_date')->after('ended_at')->nullable()->default(null);
            $table->string('payout_status', 30)->after('liquidation_reason')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->renameColumn('paid_out_payload', 'payout_response');
            $table->renameColumn('paid_out_amount', 'payout_amount');
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
            $table->dropColumn('profit_payout_type');
            $table->dropColumn('maturity_date');
            $table->dropColumn('payout_status');
            $table->dropColumn('name');
            $table->renameColumn('payout_response', 'paid_out_payload');
            $table->renameColumn('payout_amount', 'paid_out_amount');
        });
    }
}
