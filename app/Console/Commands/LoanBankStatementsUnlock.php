<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Collections\DocumentCollection;
use App\NodCredit\Loan\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanBankStatementsUnlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:bank-statements-unlock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unlock bank statements';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $documents = DocumentCollection::findBankStatementForUnlocking();

        $this->logInfo("Loaded documents count: {$documents->count()}");

        /** @var Document $document */
        foreach ($documents->all() as $document) {

            $this->logInfo("[{$document->getId()}] Attempt: {$document->getUnlockAttempts()}. Unlocking...");

            try {
                $document->unlock();
            }
            catch (\Exception $exception) {
                $this->logError("[{$document->getId()}] {$exception->getMessage()}");

                continue;
            }

            $this->logInfo("[{$document->getId()}] Unlocked.");
        }
    }

    private function log(string $type = 'info', string $message)
    {
        if (! in_array($type, ['info', 'error'])) {
            throw new \Exception("Invalid log type [{$type}]");
        }

        Log::channel('loan-documents-unlock-bank-statements')->{$type}($message);
    }

    private function logInfo(string $message)
    {
        return $this->log('info', $message);
    }

    private function logError(string $message)
    {
        return $this->log('error', $message);
    }
}
