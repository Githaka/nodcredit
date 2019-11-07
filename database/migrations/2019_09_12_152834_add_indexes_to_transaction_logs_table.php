<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToTransactionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->index('user_id', 'index_user_id');
            $table->index('card_id', 'index_card_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->dropIndex('index_user_id');
            $table->dropIndex('index_card_id');
        });
    }
}
