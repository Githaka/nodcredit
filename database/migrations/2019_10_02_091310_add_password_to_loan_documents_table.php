<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPasswordToLoanDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_documents', function (Blueprint $table) {
            $table->boolean('is_unlocked')->default(true);
            $table->string('unlock_password')->nullable()->default(null);
            $table->tinyInteger('unlock_attempts', false, true)->default(0);
            $table->text('unlock_response')->nullable()->default(null);
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
            $table->dropColumn([
                'is_unlocked',
                'unlock_password',
                'unlock_attempts',
                'unlock_response',
            ]);
        });
    }
}
