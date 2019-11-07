<?php

namespace App\Console\Commands\Investments;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Collections\InvestmentCollection;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\Notifications\FullLiquidationPaid;
use App\NodCredit\Investment\Payout;
use App\NodCredit\Message\MessageSender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PayoutFullLiquidations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:payout-full-liquidations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investments: payout full Liquidations';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $investments = InvestmentCollection::findLiquidatedForAutoPayout();

        $this->log("Loaded investments: {$investments->count()}");

        /** @var Investment $investment */
        foreach ($investments->all() as $investment) {

            try {
                Payout::fullPayout($investment);
            }
            catch (\Exception $exception) {
                $this->log("[{$investment->getId()}] Payout {$investment->getPayoutAmount()}: failed. Exception message: {$exception->getMessage()}");

                continue;
            }

            // Success
            $this->log("[{$investment->getId()}] Payout {$investment->getPayoutAmount()}: successful.");

            $investment->publicLog([
                'text' => "Transfer of full liquidation (" . Money::formatInNaira($investment->getPayoutAmount()) . ") to official bank account"
            ]);

            FullLiquidationPaid::notify($investment, 'user');
        }
    }

    /**
     * @param string $message
     */
    private function log(string $message)
    {
        Log::channel('investments-payout-full-liquidations')->info($message);
    }

}
