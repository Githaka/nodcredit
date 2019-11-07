<?php

namespace App\Console\Commands\UserScores;

use App\NodCredit\Settings;
use App\Score;
use App\User;
use Illuminate\Console\Command;

class UserScoresServe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-scores:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve user scores';
    /**
     * @var Settings
     */
    private $settings;

    /**
     * Create a new command instance.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        parent::__construct();

        $this->settings = $settings;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Delete LOAN_REJECTED scores after X days
        $this->serveLoanRejectedScores();

    }

    private function serveLoanRejectedScores()
    {

        $days = $this->settings->get('user_scores_loan_rejected_delete_after', 30);

        $date = now()->subDays($days);

        $usersId = Score::where('info', 'LOAN_REJECTED')
            ->where('created_at', '<=', $date)
            ->get(['user_id'])
            ->pluck('user_id')
            ->unique()
            ->toArray();

        $count = Score::where('info', 'LOAN_REJECTED')
            ->where('created_at', '<=', $date)
            ->delete();

        if (! $count) {
            return true;
        }

        // Recalculate scores and handle changes
        $users = User::whereIn('id', $usersId)->get();

        /** @var User $user */
        foreach ($users as $user) {
            $user->calculateScoresAndHandle();
        }

        return true;
    }
}
