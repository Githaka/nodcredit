<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->enum('trans_type', ['debit', 'credit', 'deposit', 'unknown'])->default('unknown');
            $table->text('payload')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->uuid('performed_by')->nullable();
            $table->enum('status', ['failed', 'successful', 'new', 'processing'])->default('new');
            $table->string('model')->nullable();
            $table->uuid('model_id')->nullable();
            $table->string('response_message')->nullable();
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
        Schema::dropIfExists('transaction_logs');
    }
}
