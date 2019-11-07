<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_payments', function (Blueprint $table) {
            $table->uuid('id');
            $table->decimal('amount', 10);
            $table->decimal('loan_amount', 10);
            $table->decimal('interest', 10);
            $table->unsignedInteger('month')->default(0);
            $table->string('sess', 150);
            $table->primary('id');
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
        Schema::dropIfExists('temp_payments');
    }
}
