<?php

use Illuminate\Database\Seeder;

class ModeratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\User::create([
            'name' => 'ADMINISTRATOR',
            'email' => 'admin@nodcredit.com',
            'password' => bcrypt('Chuks-1234zA'),
            'role' => 'admin',
            'phone' => '2348094278889'
        ]);
    }
}
