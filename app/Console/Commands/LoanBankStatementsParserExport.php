<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Collections\DocumentCollection;
use App\NodCredit\Loan\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanBankStatementsParserExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:bank-statements-parser-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan export uploaded bank statement documents to API';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $documents = DocumentCollection::findBankStatementsForExportingToParser();

        /** @var Document $document */
        foreach ($documents->all() as $document) {

            try {
                $document->exportBankStatementToParser();
            }
            catch (\Exception $exception) {
                Log::channel('loan-document-parsing')->info("[{$document->getId()}] send for parsing exception: {$exception->getMessage()}");

                continue;
            }

            Log::channel('loan-document-parsing')->info("[{$document->getId()}] is sent for parsing.");
        }

    }
}
