<?php

use App\Bank;
use Illuminate\Database\Seeder;

class BanksFix2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Change bank name
        Bank::where('name', 'DIAMOND BANK')->update(['name' => 'ACCESS BANK (DIAMOND)']);

    }
}
