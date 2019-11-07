<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesInMessageTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->string('title')->nullable()->default(null)->after('id');
            $table->string('channel')->default('email')->after('message');

            $table->dropColumn('message_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('channel');
            $table->dropColumn('key');

            $table->string('message_key');
        });
    }
}
