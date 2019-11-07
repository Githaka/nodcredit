<?php

use Illuminate\Database\Seeder;

use App\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();
        
        $roles = [
            ['name'=>'client', 'display_name'=>'Client', 'description'=>'Client app role'],
            ['name'=>'investor', 'display_name'=>'Investor', 'description'=>'Investor app role'],
            ['name'=>'admin', 'display_name'=>'Admin', 'description'=>'Admin app role'],
        ];

        foreach($roles as $role)
            Role::create($role);
    }
}
