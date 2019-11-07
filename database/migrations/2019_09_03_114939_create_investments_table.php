<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id');
            $table->string('status', 20)->default('new');
            $table->decimal('amount', 12, 2);
            $table->decimal('original_amount', 12, 2);
            $table->string('plan_value')->nullable()->default(null);
            $table->string('plan_name')->nullable()->default(null);
            $table->integer('plan_days');
            $table->integer('plan_percentage');
            $table->dateTime('started_at')->nullable()->default(null);
            $table->dateTime('ended_at')->nullable()->default(null);

            $table->dateTime('liquidated_at')->nullable()->default(null);
            $table->integer('liquidated_on_day')->nullable()->default(null);
            $table->uuid('liquidated_by')->nullable()->default(null);
            $table->text('liquidation_reason')->nullable()->default(null);

            $table->dateTime('paid_out_at')->nullable()->default(null);
            $table->decimal('paid_out_amount', 12, 2)->nullable()->default(null);
            $table->decimal('profit', 12, 2)->nullable()->default(null);
            $table->text('paid_out_payload')->nullable()->default(null);

            $table->uuid('created_by');
            $table->uuid('payment_id')->nullable()->default(null);

            $table->timestamps();
        });

        Schema::create('investment_partial_liquidations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('investment_id');
            $table->string('status', 20)->default('new');
            $table->decimal('amount', 12, 2);
            $table->decimal('profit', 12, 2);
            $table->decimal('user_profit', 12, 2);
            $table->decimal('profit_penalty_amount', 12, 2);
            $table->integer('profit_penalty_percent');
            $table->integer('liquidated_on_day');
            $table->uuid('created_by')->nullable()->default(null);
            $table->dateTime('paid_out_at')->nullable()->default(null);
            $table->text('paid_out_payload')->nullable()->default(null);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('investments');
        Schema::dropIfExists('investment_partial_liquidations');
    }
}
