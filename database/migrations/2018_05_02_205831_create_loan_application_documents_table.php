<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanApplicationDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_application_documents', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('filename');
            $table->string('comment')->nullable();
            $table->string('document_type', 150)->nullable();
            $table->uuid('loan_application_id');
            $table->primary('id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_application_documents');
    }
}
