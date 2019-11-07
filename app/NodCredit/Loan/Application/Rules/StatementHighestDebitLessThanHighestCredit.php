<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class StatementHighestDebitLessThanHighestCredit implements RuleInterface
{

    public static function validate(Validator $validator): bool
    {
        $debit = $validator->getStatement()->getTransactions()->getHighestDebit();
        $debitAmount = $debit ? $debit->getAmount() : 0;

        $credit = $validator->getStatement()->getTransactions()->getHighestCredit();
        $creditAmount = $credit ? $credit->getAmount() : 0;


        if ($debitAmount < $creditAmount) {
            return true;
        }

        throw new ApplicationValidateRuleException('Statement: highest Debit Amount is more than highest Credit Amount');
    }
}