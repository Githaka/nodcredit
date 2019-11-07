<?php

use App\Bank;
use Illuminate\Database\Seeder;

class BanksFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Fix bank name
        Bank::where('name', 'PARALLEX')->update(['name' => 'PARALLEX BANK']);

        // Add new
        $banks = [
            ['name' => 'POLARIS BANK', 'code' => '076'],
        ];

        foreach ($banks as $item) {
            Bank::create($item);
        }

    }
}
