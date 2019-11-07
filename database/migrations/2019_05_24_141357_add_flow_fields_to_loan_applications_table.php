<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlowFieldsToLoanApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->double('amount_allowed')->nullable()->after('amount_approved');
            $table->dateTime('amount_allowed_at')->nullable()->after('amount_allowed');
            $table->dateTime('approval_at')->nullable()->after('amount_allowed_at');
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
            $table->dropColumn('amount_allowed');
            $table->dropColumn('amount_allowed_at');
            $table->dropColumn('approval_at');
        });
    }
}
