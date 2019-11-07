<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Application\Automation;
use App\NodCredit\Loan\Collections\ApplicationCollection;
use Illuminate\Console\Command;

class LoanApplicationHandleApproval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:handle-approval {--users=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle Loan Applications with approval status and which are ready for pay out.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Filter by users: "existing" or "all"
        if ($this->option('users') === 'existing') {
            $applications = ApplicationCollection::findReadyForPayOutForExistingUsers();
        }
        else {
            $applications = ApplicationCollection::findReadyForPayOut();
        }

        foreach ($applications->all() as $application) {
            Automation::handle($application);
        }

    }
}
