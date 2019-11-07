<?php

use Illuminate\Database\Seeder;

class InvestmentSettingsSeeder extends Seeder
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
                'k' => 'investment_max_auto_payout',
                'v' => 500000,
                'description' => 'Max amount for investment auto payout (partial liquidation, full liquidation).',
                'type' => 'integer',
                'group' => 'investment'
            ],
            [
                'k' => 'investment_liquidation_penalty',
                'v' => 40,
                'description' => 'Liquidation charge (%) of interest earned as at pre-liquidation.',
                'type' => 'integer',
                'group' => 'investment'
            ],
            [
                'k' => 'investment_min_amount',
                'v' => 10000,
                'description' => 'Minimum Deposit',
                'type' => 'integer',
                'group' => 'investment'
            ],
            [
                'k' => 'investment_max_amount',
                'v' => 1000000,
                'description' => 'Maximum Deposit',
                'type' => 'integer',
                'group' => 'investment'
            ],
            [
                'k' => 'investment_default_withholding_tax',
                'v' => 10,
                'description' => 'Withholding tax applies to scheduled interest payouts.',
                'type' => 'integer',
                'group' => 'investment'
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
