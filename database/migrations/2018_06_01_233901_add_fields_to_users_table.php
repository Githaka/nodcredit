<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('newsletter')->nullable();
            $table->dateTime('track_usage')->nullable();
            $table->enum('gender', ['male', 'female', 'others'])->default('others');
            $table->date('dob')->nullable();
            $table->dateTime('phone_verified')->nullable();
            $table->dateTime('email_verified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['newsletter', 'track_usage', 'gender', 'dob', 'phone_verified', 'email_verified']);
        });
    }
}
