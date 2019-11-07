<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanRangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_ranges', function (Blueprint $table) {
            $table->uuid('id');
            $table->decimal('min', 12, 2);
            $table->decimal('max', 12, 2);
            $table->unsignedInteger('pay_month')->default(1);
            $table->primary('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_ranges');
    }
}
