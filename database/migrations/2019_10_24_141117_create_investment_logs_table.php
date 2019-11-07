<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestmentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investment_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('investment_id');
            $table->uuid('created_by')->nullable();
            $table->text('text')->nullable();
            $table->text('payload')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

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
        Schema::dropIfExists('investment_logs');
    }
}
