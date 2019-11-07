<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class LoanAmountLessThanStatementMonthlyAvgCreditsAmount implements RuleInterface
{

    public static function validate(Validator $validator): bool
    {
        $loanAmount = $validator->getLoanApplication()->amount_requested;

        $monthlyAvgAmount = $validator->getStatement()->getMonthlyAvgCreditsAmount();

        if ($loanAmount <= $monthlyAvgAmount * 0.33) {
            return true;
        }

        throw new ApplicationValidateRuleException('Loan amount is more than Statement monthly average credits amount.');
    }
}