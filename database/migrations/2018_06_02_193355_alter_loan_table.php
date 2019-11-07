<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLoanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_documents', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });

        Schema::table('loan_documents', function (Blueprint $table) {
            $table->uuid('document_type');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_documents', function (Blueprint $table) {
            $table->uuid('document_type');
        });
    }
}
