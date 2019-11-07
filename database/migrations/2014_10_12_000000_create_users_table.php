<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email', 150)->unique();
            $table->string('phone', 150)->unique();
            $table->string('password');
            $table->string('bvn', 40)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('last_login')->nullable();
            $table->string('avatar')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('bank', 100)->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('recipient_code')->nullable();
            $table->enum('role', ['user', 'admin','partner','support'])->nullable()->default('user');
            $table->dateTime('newsletter')->nullable();
            $table->dateTime('track_usage')->nullable();
            $table->enum('gender', ['male', 'female','others'])->nullable()->default('others');
            $table->date('dob')->nullable();
            $table->dateTime('phone_verified')->nullable();
            $table->dateTime('email_verified')->nullable();
            $table->double('balance', 12, 2)->default(0.00);
            $table->string('bvn_phone')->nullable();
            $table->tinyInteger('force_change_pwd')->default(0);
            $table->double('scores', 12, 2)->nullable()->default(0.00);
            $table->dateTime('banned_at')->nullable();
            $table->text('ban_reason')->nullable();
            $table->tinyInteger('is_app_install_skipped')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
