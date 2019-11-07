<?php

namespace App\Console\Commands\Issues;

use App\LoanApplication;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Issue2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'issues:issue-2 {--scores=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for issue 2. Can be deleted later.';

    private $allowedScores = [
        'SUCCESSFUL_USER_REGISTERATION',
        'LOAN_APPROVED',
        'SUCCESSFULLY_ADDED_DETAILS'
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('scores') === 'SUCCESSFUL_USER_REGISTERATION') {
            return $this->fixSuccessfulUserRegisterationScores();
        }
        else if ($this->option('scores') === 'LOAN_APPROVED') {
            return $this->fixLoanApprovedScores();
        }
        else if ($this->option('scores') === 'SUCCESSFULLY_ADDED_DETAILS') {
            return $this->fixSuccessfullyAddedDetailsScores();
        }

        $this->error('Invalid scores type. Allowed types: ' . implode(', ', $this->allowedScores));
        $this->error('Example: issues:issue-2 --scores=' . array_get($this->allowedScores, 0));
    }

    private function fixLoanApprovedScores()
    {
        // Remove LOAN_APPROVED scores
        DB::table('scores')->where('info', 'LOAN_APPROVED')->delete();

        // Re-calculate LOAN_APPROVED scores
        $approved = LoanApplication::with('owner')->whereIn('status', ['approved', 'completed'])->get();

        foreach ($approved as $loan) {
            $loan->owner->getScoreInfo('LOAN_APPROVED', $loan->amount_approved);
        }

    }

    private function fixSuccessfulUserRegisterationScores()
    {

        // Remove SUCCESSFUL_USER_REGISTERATION scores
        $deletedCount = DB::table('scores')->where('info', 'SUCCESSFUL_USER_REGISTERATION')->delete();

        $successfulRegisteredUsers = User::whereNotNull('phone_verified')->get();

        /** @var User $user */
        foreach ($successfulRegisteredUsers as $user) {
            // Add scores
            $user->getScoreInfo('SUCCESSFUL_USER_REGISTERATION');
        }

        $this->info('Deleted scores records: ' . $deletedCount);
        $this->info('Added scores records: ' . $successfulRegisteredUsers->count());
    }


    private function fixSuccessfullyAddedDetailsScores()
    {

        // Remove SUCCESSFULLY_ADDED_DETAILS scores
        $deletedCount = DB::table('scores')->where('info', 'SUCCESSFULLY_ADDED_DETAILS')->delete();

        $users = User::all();
        $addedCount = 0;

        /** @var User $user */
        foreach ($users as $user) {
            // Validate scores
            if ($user->validateSuccessfullyAddedDetailsScore()) {
                $addedCount++;
            }
        }

        $this->info('Deleted scores records: ' . $deletedCount);
        $this->info('Added scores records: ' . $addedCount);
    }
}
