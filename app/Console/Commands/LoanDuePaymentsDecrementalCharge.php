<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\DecrementalConfig;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Loan\PaymentDecrementalCardCharge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanDuePaymentsDecrementalCharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-decremental-charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments decremental charge';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $duePayments = PaymentCollection::findDueForOrMore(6, 2);

        $decrementalConfig = new DecrementalConfig(50, 100);

        $this->log("Start 'decremental charge'. Loaded due payments: {$duePayments->count()}");

        /** @var Payment $payment */
        foreach ($duePayments->all() as $payment) {

            $charger = new PaymentDecrementalCardCharge($payment, $decrementalConfig);

            $this->log("[{$payment->getId()}] Start decremental charging.");

            try {
                $charger->charge();
            }
            catch (\Exception $exception) {
                $this->log("[{$payment->getId()}] Exception message: " . $exception->getMessage());

                continue;
            }
        }

        $this->log("Stop 'decremental charge'.");
    }

    private function log(string $message)
    {
        Log::channel('loan-decremental-charge')->info($message);
    }
}
