<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLoanDocumentTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_document_types', function (Blueprint $table) {
            $table->boolean('is_required')->default(false);
            $table->string('file_type')->default('*');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_document_types', function (Blueprint $table) {
            $table->dropColumn(['is_required', 'file_type']);
        });
    }
}
