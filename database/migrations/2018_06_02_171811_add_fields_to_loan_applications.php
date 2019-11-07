<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLoanApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->enum('status', ['new', 'processing', 'approved', 'rejected', 'unknown', 'completed', 'approval'])->default('new');
            $table->decimal('interest_rate')->default(0);
            $table->integer('tenor')->default(1);
            $table->dateTime('approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_at']);
        });
    }
}
