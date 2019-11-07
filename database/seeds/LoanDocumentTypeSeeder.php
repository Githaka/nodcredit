<?php

use Illuminate\Database\Seeder;

class LoanDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\LoanDocumentType::create(['name' => 'Account Statement']);
        \App\LoanDocumentType::create(['name' => 'Passport']);
    }
}
