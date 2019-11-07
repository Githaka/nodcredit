<?php

namespace App\Console\Commands\Investments;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Collections\ProfitPaymentCollection;
use App\NodCredit\Investment\Notifications\InvestmentProfitPaymentPaid;
use App\NodCredit\Investment\Payout;
use App\NodCredit\Investment\ProfitPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PayoutProfitPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:payout-profit-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investments: payout profit payments';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $profitPayments = ProfitPaymentCollection::findForAutoPayout();

        $this->log("Loaded profit payments: {$profitPayments->count()}");

        /** @var ProfitPayment $profitPayment */
        foreach ($profitPayments->all() as $profitPayment) {

            try {
                Payout::profitPayout($profitPayment);
            }
            catch (\Exception $exception) {
                $this->log("[{$profitPayment->getId()}] Payout {$profitPayment->getAmount()}: failed.");

                continue;
            }

            // Success
            $this->log("[{$profitPayment->getId()}] Payout {$profitPayment->getAmount()}: successful.");

            $profitPayment->getInvestment()->publicLog([
                'text' => "Transfer of interest (" . Money::formatInNaira($profitPayment->getAmount()) . ") to official bank account"
            ]);

            InvestmentProfitPaymentPaid::notify($profitPayment, 'all');
        }

    }

    /**
     * @param string $message
     */
    private function log(string $message)
    {
        Log::channel('investments-payout-profit-payments')->info($message);
    }

}
