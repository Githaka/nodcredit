<?php

namespace App\Console\Commands\Investments;

use App\NodCredit\Investment\Collections\InvestmentCollection;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\Notifications\InvestmentCompleted;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CompleteMatureInvestments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:complete-mature-investments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investments: complete mature investments';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $investments = InvestmentCollection::findMatureForCompleting();

        $this->log("Loaded investments: {$investments->count()}");

        /** @var Investment $investment */
        foreach ($investments->all() as $investment) {

            $investment->end();

            if (! $investment->isEnded()) {
                $this->log("[{$investment->getId()}] Ending failed.");

                continue;
            }

            $investment->publicLog([
                'text' => "Investment has completed."
            ]);

            $this->log("[{$investment->getId()}] Ended successfully.");

            InvestmentCompleted::notify($investment, 'all');
        }
    }

    /**
     * @param string $message
     */
    private function log(string $message)
    {
        Log::channel('investments-complete-mature-investments')->info($message);
    }

}
