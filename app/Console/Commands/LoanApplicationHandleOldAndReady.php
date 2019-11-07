<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Application\Automation;
use App\NodCredit\Loan\Collections\ApplicationCollection;
use Illuminate\Console\Command;

class LoanApplicationHandleOldAndReady extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:handle-old-and-ready {--users=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle old Loan Applications with new status and which are ready for processing but was not handled before.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        // Filter by users: "existing" or "all"
        if ($this->option('users') === 'existing') {
            $applications = ApplicationCollection::findOldAndReadyForExistingUsers(1, 30);
        }
        else {
            $applications = ApplicationCollection::findOldAndReady(1, 30);
        }

        foreach ($applications->all() as $application) {
            Automation::sendHandlingConfirmationMail($application);
        }
    }
}
