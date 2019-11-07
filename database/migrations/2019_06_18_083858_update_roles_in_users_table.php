<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRolesInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add support role
        $roles = ['user', 'admin', 'partner', 'support'];

        DB::statement("
            ALTER TABLE `users` 
            CHANGE COLUMN `role` `role` ENUM('" . implode("','", $roles) . "') NOT NULL DEFAULT 'user'"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Return prev roles
        $roles = ['user', 'admin', 'partner'];

        DB::statement("
            ALTER TABLE `users` 
            CHANGE COLUMN `role` `role` ENUM('" . implode("','", $roles) . "') NOT NULL DEFAULT 'user'"
        );
    }
}
