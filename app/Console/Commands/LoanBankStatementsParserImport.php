<?php

namespace App\Console\Commands;

use App\NodCredit\Loan\Collections\DocumentCollection;
use App\NodCredit\Loan\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanBankStatementsParserImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:bank-statements-parser-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan import parsed results';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $documents = DocumentCollection::findBankStatementsForImportingFromParser();

        /** @var Document $document */
        foreach ($documents->all() as $document) {

            try {
                $document->importBankStatementFromParser();
            }
            catch (\Exception $exception) {
                Log::channel('loan-document-parsing')->info("[{$document->getId()}] Parser import exception: {$exception->getMessage()}");

                continue;
            }

            Log::channel('loan-document-parsing')->info("[{$document->getId()}] imported from Parser.");
        }

    }
}
