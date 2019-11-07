<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class LoanAmountLessThanStatementHighestCreditAmount implements RuleInterface
{

    public static function validate(Validator $validator): bool
    {
        $loanAmount = $validator->getLoanApplication()->amount_requested;

        $credit = $validator->getStatement()->getTransactions()->getHighestCredit();

        $creditAmount = $credit ? $credit->getAmount() : 0;

        if ($loanAmount < $creditAmount) {
            return true;
        }

        throw new ApplicationValidateRuleException('Loan amount is more than Statement highest credit amount');
    }
}