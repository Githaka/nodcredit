<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanDuePaymentsApplyPenalty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-apply-penalty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments penalty handler';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $duePayments = PaymentCollection::findForPenalties();

        $this->log("Loaded due payments: {$duePayments->count()}");

        /** @var Payment $payment */
        foreach ($duePayments->all() as $payment) {
            try {
                $payment->increaseAmountBy(1);

                $oldAmount = $payment->getAmount() / 1.01;

                $this->log("Payment [{$payment->getId()}]. Amount increased: {$oldAmount} => {$payment->getAmount()}");
            }
            catch (\Exception $exception) {
                continue;
            }
        }
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = [])
    {
        Log::channel('loan-due-payments-apply-penalty')->info($message, $context);
    }

}
