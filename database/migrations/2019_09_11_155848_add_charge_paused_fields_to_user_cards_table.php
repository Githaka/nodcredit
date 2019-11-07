<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChargePausedFieldsToUserCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->dateTime('charging_paused_at')->nullable()->default(null);
            $table->dateTime('charging_paused_until')->nullable()->default(null);
            $table->string('charging_pause_reason')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->dropColumn('charging_paused_at');
            $table->dropColumn('charging_paused_until');
            $table->dropColumn('charging_pause_reason');
        });
    }
}
