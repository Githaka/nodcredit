<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanPaymenStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_payment_states', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->nullable();
            $table->uuid('loan_application_id')->nullable();
            $table->uuid('loan_payment_id')->nullable();
            $table->string('action', 50)->nullable();
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
        Schema::dropIfExists('loan_payment_states');
    }
}
