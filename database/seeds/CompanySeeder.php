<?php

use Illuminate\Database\Seeder;
use App\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::truncate();

        $reader = CsvReader::open(realpath(dirname(__FILE__) . '/csv/companies.csv'));

        $header = $reader->readLine();

        while (($line = $reader->readLine()) !== false) {
            try {
                Company::create([
                    'name' => $line[1],
                    'interest_rate_flat_monthly' => $line[2],
                    'tenor_per_month' => $line[3],
                    'max_loan_amount' => $line[4] * 1000,
                    'length_of_service' => $line[5],
                ]);
            } catch (Exception $e){

            }
        }
    }
}
