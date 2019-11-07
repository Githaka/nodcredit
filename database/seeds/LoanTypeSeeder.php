<?php

use Illuminate\Database\Seeder;

use App\LoanType;

class LoanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LoanType::truncate();
        $loanTypes = [
            "Home Improvement",
            "Household Expenses",
            "Salary Supplement",
            "Medical Expenses",
            "Debt Repayment",
            "Car Repair",
            "Travel",
            "Rent",
            "Other"
        ];

        foreach($loanTypes as $loanType)
            LoanType::create(['name' => $loanType]);
    }
}
