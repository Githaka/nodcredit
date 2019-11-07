<?php

namespace App\Console\Commands\Investments;

use App\NodCredit\Investment\Collections\ProfitPaymentCollection;
use App\NodCredit\Investment\Notifications\InvestmentProfitPaymentPaid;
use App\NodCredit\Investment\Notifications\InvestmentProfitPaymentReminder;
use App\NodCredit\Investment\Payout;
use App\NodCredit\Investment\ProfitPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProfitPaymentsPayoutReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:profit-payments-payout-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investments: profit payments payout reminder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $profitPayments = ProfitPaymentCollection::findScheduledInHours(24);

        $this->log("Loaded profit payments: {$profitPayments->count()}");

        /** @var ProfitPayment $profitPayment */
        foreach ($profitPayments->all() as $profitPayment) {
            InvestmentProfitPaymentReminder::notify($profitPayment, 'all');
        }

    }

    /**
     * @param string $message
     */
    private function log(string $message)
    {
        Log::channel('investments-profit-payments-payout-reminder')->info($message);
    }

}
