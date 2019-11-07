<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = ['admin', 'client', 'investor'];
        foreach ($roles as $i => $roleName) {

            $role = Role::where(['name' => $roleName])->first();

            $user = User::create([
                'name' => ucfirst($roleName),
                'email' => $roleName . '@gmail.com',
                'password' => '1234567',
                'phone' => '12345678'.$i
            ]);

            $user->roles()->attach([$role->id]);
        }
    }
}
