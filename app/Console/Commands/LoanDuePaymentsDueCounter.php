<?php

namespace App\Console\Commands;

use App\Events\SendMessage;
use App\Message;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\LoanPause;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanDuePaymentsDueCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-due-counter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments handle due counter';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var Payment $payment */

        $secondTimeDue = PaymentCollection::findDueFor(1, 1);

        foreach ($secondTimeDue->all() as $payment) {
            $payment->increaseDueCount();
        }

        $firstTimeDue = PaymentCollection::findDueFor(1, 0);

        foreach ($firstTimeDue->all() as $payment) {
            $payment->increaseDueCount();
        }

        $this->log('First time due payments: ' . $firstTimeDue->count());
        $this->log('Second time due payments: ' . $secondTimeDue->count());
    }

    private function log(string $message, array $context = [])
    {
        Log::channel('loan-due-payments-due-counter')->info($message, $context);
    }

}
