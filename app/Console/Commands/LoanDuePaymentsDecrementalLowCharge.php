<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\DecrementalConfig;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Loan\PaymentDecrementalCardCharge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanDuePaymentsDecrementalLowCharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-decremental-low-charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments decremental charge low amounts';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $duePayments = PaymentCollection::findDueForOrMore(1, 2);

        $decrementalConfigLow = new DecrementalConfig(50, 100, 2, 10);
        $decrementalConfigMedium = new DecrementalConfig(50, 100, 2, 4);
        $decrementalConfigHigh = new DecrementalConfig(50, 100, 2, 2);

        $this->log("Start 'decremental low charge'. Loaded due payments: {$duePayments->count()}");

        /** @var Payment $payment */
        foreach ($duePayments->all() as $payment) {

            // Determine config by amount
            $amount = $payment->getAmount();

            if ($amount > 99999) {
                $charger = new PaymentDecrementalCardCharge($payment, $decrementalConfigHigh);
            }
            else if ($amount > 49999) {
                $charger = new PaymentDecrementalCardCharge($payment, $decrementalConfigMedium);
            }
            else {
                $charger = new PaymentDecrementalCardCharge($payment, $decrementalConfigLow);
            }

            $this->log("[{$payment->getId()}] Start decremental charging. Payment amount: {$amount}");

            try {
                $charger->charge();
            }
            catch (\Exception $exception) {
                $this->log("[{$payment->getId()}] Exception message: " . $exception->getMessage());
            }

        }

        $this->log("Stop 'decremental low charge'.");
    }

    private function log(string $message)
    {
        Log::channel('loan-due-payments-low-charge')->info($message);
    }
}
