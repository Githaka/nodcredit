<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->uuid('id');
            $table->dateTime('due_at');
            $table->decimal('amount');
            $table->uuid('loan_application_id');
            $table->enum('status', ['paid', 'failed', 'processing', 'declined', 'scheduled'])->default('scheduled');
            $table->string('payment_info')->nullable();
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
        Schema::dropIfExists('loan_payments');
    }
}
