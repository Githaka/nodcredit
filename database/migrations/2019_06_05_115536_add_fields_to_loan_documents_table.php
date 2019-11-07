<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLoanDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_documents', function (Blueprint $table) {
            $table->string('parser')->nullable()->default(null);
            $table->string('parser_external_id')->nullable()->default(null);
            $table->longText('parser_payload')->nullable()->default(null);
            $table->longText('parsed_data')->nullable()->default(null);
            $table->dateTime('parser_sent_at')->nullable()->default(null);
            $table->string('parser_status')->nullable()->default(null);
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
            $table->dropColumn('parser');
            $table->dropColumn('parser_external_id');
            $table->dropColumn('parser_payload');
            $table->dropColumn('parsed_data');
            $table->dropColumn('parser_sent_at');
            $table->dropColumn('parser_status');
        });
    }
}
