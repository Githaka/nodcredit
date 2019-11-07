<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Application\Automation;
use App\NodCredit\Loan\Collections\ApplicationCollection;
use Illuminate\Console\Command;

class LoanApplicationHandleProcessingAndHandledByParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:handle-processing-and-handled-by-parser {--users=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle Loan Applications with processing status, handled by parser and which are ready for validation.';

    public function handle()
    {

        // Filter by users: "existing" or "all"
        if ($this->option('users') === 'existing') {
            $applications = ApplicationCollection::findProcessingAndHandledByParserForExistingUsers();
        }
        else {
            $applications = ApplicationCollection::findProcessingAndHandledByParser();
        }

        foreach ($applications->all() as $application) {
            Automation::handle($application);
        }
    }
}
