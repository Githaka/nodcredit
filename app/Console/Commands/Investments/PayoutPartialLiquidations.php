<?php

namespace App\Console\Commands\Investments;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Collections\PartialLiquidationCollection;
use App\NodCredit\Investment\Notifications\PartialLiquidationPaid;
use App\NodCredit\Investment\PartialLiquidation;
use App\NodCredit\Investment\Payout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PayoutPartialLiquidations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:payout-partial-liquidations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investments: payout Partial Liquidations';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $partials = PartialLiquidationCollection::findForAutoPayout();

        $this->log("Loaded: {$partials->count()}");

        /** @var PartialLiquidation $partial */
        foreach ($partials->all() as $partial) {

            try {
                Payout::partialPayout($partial);
            }
            catch (\Exception $exception) {
                $this->log("[{$partial->getId()}] Payout {$partial->getAmount()}: failed. Exception message: {$exception->getMessage()}");

                continue;
            }

            // Success
            $this->log("[{$partial->getId()}] Payout {$partial->getAmount()}: successful.");

            $partial->getInvestment()->publicLog([
                'text' => "Transfer of partial liquidation (" . Money::formatInNaira($partial->getAmount()) . ") to official bank account"
            ]);

            PartialLiquidationPaid::notify($partial, 'user');
        }
    }

    /**
     * @param string $message
     */
    private function log(string $message)
    {
        Log::channel('investments-payout-partial-liquidations')->info($message);
    }

}
