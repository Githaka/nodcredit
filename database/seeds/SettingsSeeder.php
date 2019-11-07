<?php

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [
            [
                'k' => 'loan_due_payments_penalty_pause_days',
                'v' => 5,
                'description' => 'Loan due payments penalty pause days',
                'type' => 'integer',
                'group' => 'loan_due_payments'
            ],
            [
                'k' => 'loan_due_payments_penalty_pause_threshold',
                'v' => 20,
                'description' => 'Loan due payments penalty pause threshold (% of amount)',
                'type' => 'integer',
                'group' => 'loan_due_payments'
            ],
            [
                'k' => 'user_location_and_contact_valid_age',
                'v' => 60,
                'description' => 'Location and Contact valid age in days',
                'type' => 'integer',
                'group' => 'user'
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
