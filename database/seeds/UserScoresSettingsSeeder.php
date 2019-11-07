<?php

use Illuminate\Database\Seeder;

class UserScoresSettingsSeeder extends Seeder
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
                'k' => 'user_scores_loan_rejected_delete_after',
                'v' => 30,
                'description' => 'Delete LOAN_REJECTED scores after X days',
                'type' => 'integer',
                'group' => 'user_scores'
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
