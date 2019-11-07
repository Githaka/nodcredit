<?php

use Illuminate\Database\Seeder;

class AutomationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [
            ['k' => 'automation_rule_loan_amount_less_than_statement_highest_credit_amount_deduct', 'v' => 5],
            ['k' => 'automation_rule_statement_highest_debit_less_than_highest_credit_deduct', 'v' => 5],
            ['k' => 'automation_rule_statement_last_month_highest_credit_protected_deduct', 'v' => 5],
            ['k' => 'automation_rule_statement_last_month_highest_credit_protected_times', 'v' => 2],
            ['k' => 'automation_rule_statement_last_month_highest_credit_protected_percent', 'v' => 20],
            ['k' => 'automation_rule_statement_period_more_than_month_deduct', 'v' => 10],
            ['k' => 'automation_active', 'v' => 0],
            ['k' => 'automation_rule_lender_list', 'v' => 'fairmoney,paylater,kwikmoney,renmoney,Aellacredit,zedvance,kiakia'],
            ['k' => 'automation_rule_lender_deduct_percent', 'v' => 5],
            [
                'k' => 'automation_rule_loan_amount_less_than_statement_monthly_avg_credits_amount',
                'v' => 33,
                'description' => 'Make new "valid loan amount". % of [average monthly credits amount]',
                'type' => 'integer',
                'group' => 'automation'
            ],
            [
                'k' => 'automation_rule_inflate_credits_new_valid_amount',
                'v' => 10,
                'description' => 'Make new "valid loan amount". % of [highest credit amount]',
                'type' => 'integer',
                'group' => 'automation'
            ],
            [
                'k' => 'automation_rule_inflate_credits_compare_percent',
                'v' => 20,
                'description' => '% of highest credit against second highest credit',
                'type' => 'integer',
                'group' => 'automation'
            ],
            [
                'k' => 'automation_rule_app_install_skipped_deduct',
                'v' => 30,
                'description' => 'Deduct (%) if customer did not install mobile app and confirmed "no device"',
                'type' => 'integer',
                'group' => 'automation'
            ],
        ];

        foreach ($array as $item) {
            $exists = \App\Setting::where('k', $item['k'])->first();

            if (! $exists) {
                \App\Setting::create($item);
            }
        }
    }
}
