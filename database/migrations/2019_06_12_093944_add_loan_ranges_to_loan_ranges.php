<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLoanRangesToLoanRanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_ranges', function (Blueprint $table) {
            $table->decimal('min_score')->default(0);
            $table->decimal('max_score')->default(0);
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
            $table->dropColumn(['min_score', 'max_score']);
        });
    }
}
