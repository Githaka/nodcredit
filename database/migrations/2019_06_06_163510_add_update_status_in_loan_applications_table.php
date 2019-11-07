<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdateStatusInLoanApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Added waiting status
        $statuses = ['new', 'processing', 'approved', 'rejected', 'unknown', 'completed', 'approval', 'waiting'];

        DB::statement("
          ALTER TABLE `loan_applications` 
          CHANGE COLUMN `status` `status` ENUM('" . implode("','", $statuses) . "') NOT NULL DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $statuses = ['new', 'processing', 'approved', 'rejected', 'unknown', 'completed', 'approval'];

        DB::statement("
          ALTER TABLE `loan_applications` 
          CHANGE COLUMN `status` `status` ENUM('" . implode("','", $statuses) . "') NOT NULL DEFAULT 'new'");
    }
}
