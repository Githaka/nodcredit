<?php
namespace App\NodCredit\Loan\Document;

use App\LoanDocument as Model;
use App\NodCredit\Loan\Document;
use App\NodCredit\Loan\Exceptions\BankStatementException;
use App\NodCredit\Statement\Statement;
use App\NodCredit\Statement\Transaction;
use App\NodCredit\Statement\Transactions;
use App\User;
use Carbon\Carbon;
use Docparser\Docparser;

class ParsedDataToStatement
{

    public static function convert(Document $document)
    {
        $parsedObject = $document->getParsedDataAsObject();

        if (! $parsedObject) {
            return null;
        }

        $transactions = new Transactions();

        if ($parsedObject->transactions) {
            foreach ($parsedObject->transactions as $record) {

                if ((float) $record->debit > 0) {
                    $type = Transaction::TYPE_DEBIT;
                    $amount = (float) $record->debit;
                }
                else {
                    $type = Transaction::TYPE_CREDIT;
                    $amount = (float) $record->credit;
                }

                try {
                    $transactionDate = Carbon::createFromFormat('Y-m-d', $record->transaction_date);
                }
                // Skip record without date
                catch (\Exception $exception) {
                    continue;
                }

                try {
                    $valueDate = Carbon::createFromFormat('Y-m-d', $record->value_date);
                }
                // Fallback date
                catch (\Exception $exception) {
                    $valueDate = $transactionDate;
                }

                $reference = isset($record->reference) ? $record->reference : '';
                $description = isset($record->description) ? $record->description : '';

                $transaction = new Transaction(
                    $type,
                    $amount,
                    (float) $record->balance,
                    $transactionDate,
                    $valueDate,
                    $reference,
                    $description
                );

                $transactions->push($transaction);
            }
        }

        $statement = new Statement();

        $statement
            ->setAccountNumber($parsedObject->account_number)
            ->setCustomerName($parsedObject->customer_name)
            ->setTransactions($transactions)
        ;

        try {
            $periodStart = Carbon::createFromFormat('Y-m-d', $parsedObject->statement_period_start);

            $statement->setStartAt($periodStart);
        }
        catch (\Exception $exception) {
            if (! $statement->getTransactions()->count()) {
                return null;
            }

            $statement->useFirstTransactionAsStartAt();
        }

        try {
            $periodEndAt = Carbon::createFromFormat('Y-m-d', $parsedObject->statement_period_end);

            $statement->setEndAt($periodEndAt);
        }
        catch (\Exception $exception) {
            if (! $statement->getTransactions()->count()) {
                return null;
            }

            $statement->useLastTransactionAsEndAt();
        }

        return $statement;
    }

}