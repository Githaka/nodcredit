<?php

namespace App\Console\Commands\Investments;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Collections\InvestmentCollection;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\Notifications\CompletedInvestmentPaid;
use App\NodCredit\Investment\Payout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PayoutCompletedInvestments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:payout-completed-investments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investments: payout completed investments';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $investments = InvestmentCollection::findCompletedForAutoPayout();

        $this->log("Loaded investments: {$investments->count()}");

        /** @var Investment $investment */
        foreach ($investments->all() as $investment) {

            try {
                Payout::fullPayout($investment);
            }
            catch (\Exception $exception) {
                $this->log("[{$investment->getId()}] Payout {$investment->getAmount()}: failed. Exception message: {$exception->getMessage()}");

                continue;
            }

            $investment->publicLog([
                'text' => "Investment is completed and we have made a transfer of " . Money::formatInNaira($investment->getPayoutAmount()) . " to official bank account"
            ]);

            // Success
            $this->log("[{$investment->getId()}] Payout {$investment->getAmount()}: successful.");

            CompletedInvestmentPaid::notify($investment, 'all');
        }

    }

    /**
     * @param string $message
     */
    private function log(string $message)
    {
        Log::channel('investments-payout-completed-investments')->info($message);
    }

}
