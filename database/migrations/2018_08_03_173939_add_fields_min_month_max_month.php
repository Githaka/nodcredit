<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsMinMonthMaxMonth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_ranges', function (Blueprint $table) {
            $table->unsignedInteger('min_month')->default(1);
            $table->unsignedInteger('max_month')->default(12);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_ranges', function (Blueprint $table) {
            $table->dropColumn(['min_month', 'max_month']);
        });
    }
}
