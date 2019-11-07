<?php

namespace App\Console\Commands\UserScores;

use App\User;
use Illuminate\Console\Command;

class UserScoresCalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-scores:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate user scores';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $users = User::all();

        foreach ($users as $user) {
            $user->calculateScores();
        }

    }

}
