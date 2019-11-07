<?php

namespace App\Console\Commands\UserScores;

use App\LoanPayment;
use App\NodCredit\Loan\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GiveLoanPastDueDateScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-scores:give-loan-past-due-date-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give LOAN_PAST_DUE_DATE scores';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = \App\ScoreConfig::where('name', 'LOAN_PAST_DUE_DATE')->first();

        if (! $config) {
            $this->log('LOAN_PAST_DUE_DATE config not found.');
            return true;
        }

        $frequencies = $config->frequencies;

        if (! is_array($frequencies) OR ! count($frequencies)) {
            $this->log('Invalid LOAN_PAST_DUE_DATE config.');
            return true;
        }

        $today = now();

        $payments = LoanPayment::with('loan.owner')
            ->where('status', LoanPayment::STATUS_SCHEDULED)
            ->where('due_count', 2)
            ->whereHas('loan', function($query) {
                $query->where('status', Application::STATUS_APPROVED)->whereNotNull('paid_out');
            })
            ->get();

        if (! $payments->count()) {
            $this->log('No payments.');

            return true;
        }

        $scoreByDays = [];

        foreach ($frequencies as $frequency) {
            $scoreByDays[array_get($frequency, 'amount')] = array_get($frequency, 'score', 0);
        }

        foreach ($payments as $payment) {

            $paymentDueDays = $payment->due_at->diffInDays($today);

            // Give scores
            if ($scores = array_get($scoreByDays, $paymentDueDays, false)) {
                $payment->loan->owner->giveScore($scores, 'LOAN_PAST_DUE_DATE');
                $this->log("User [{$payment->loan->owner->id}], payment [{$payment->id}]. Past [$paymentDueDays] due days. Added [$scores] scores ");
            }
        }

    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = [])
    {
        Log::channel('user-scores-loan-past-due-date')->info($message, $context);
    }

}
