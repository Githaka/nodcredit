<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_contacts', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id');
            $table->uuid('external_id')->nullable();
            $table->string('name')->nullable();
            $table->boolean('starred')->nullable();
            $table->string('in_visible_group')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
            $table->index('user_id', 'user_id_index');
            $table->index(['user_id', 'external_id'], 'user_id_external_id_index');
        });

        Schema::create('user_contact_emails', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('contact_id');
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
            $table->index('contact_id', 'contact_id_index');
            $table->index('email', 'email_index');
        });

        Schema::create('user_contact_phones', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('contact_id');
            $table->string('phone')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
            $table->index('contact_id', 'contact_id_index');
            $table->index('phone', 'phone_index');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_contacts');
        Schema::dropIfExists('user_contact_emails');
        Schema::dropIfExists('user_contact_phones');
    }
}
