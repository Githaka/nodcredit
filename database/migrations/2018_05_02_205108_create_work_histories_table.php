<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_histories', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id');
            $table->string('employer_name')->nullable();
            $table->string('work_industry')->nullable();
            $table->string('work_address')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('work_email')->nullable();
            $table->string('work_website')->nullable();
            $table->boolean('is_current')->nullable();
            $table->date('started_date')->nullable();
            $table->date('stopped_date')->nullable();
            $table->primary('id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_histories');
    }
}
