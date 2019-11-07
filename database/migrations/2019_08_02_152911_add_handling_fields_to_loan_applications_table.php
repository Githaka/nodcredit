<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHandlingFieldsToLoanApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->string('handling_confirmation_token', 64)->nullable()->default(null);
            $table->dateTime('handling_confirmation_sent_at')->nullable()->default(null);
            $table->dateTime('handling_confirmed_at')->nullable()->default(null);
            $table->dateTime('handling_rejected_at')->nullable()->default(null);
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
            $table->dropColumn('handling_confirmation_token');
            $table->dropColumn('handling_confirmation_sent_at');
            $table->dropColumn('handling_confirmed_at');
            $table->dropColumn('handling_rejected_at');
        });
    }
}
