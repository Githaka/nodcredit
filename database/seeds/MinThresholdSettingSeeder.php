<?php

use Illuminate\Database\Seeder;

class MinThresholdSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = ['k' => 'automation_rule_min_threshold_amount', 'v' => 10000];

        $exists = \App\Setting::where('k', $array['k'])->first();

        if (! $exists) {
            \App\Setting::create($array);
        }
    }
}
